<?php
namespace Admin\Model;
use Think\Model;
class GoodsModel extends Model{

	protected $insertFields = 'goods_name,market_price,shop_price,goods_desc,is_on_sale,brand_id,cat_id,type_id,promote_price,promote_start_date,promote_end_date,is_new,is_hot,is_best,sort_num,is_floor';
	protected $updateFields = 'id,goods_name,market_price,shop_price,goods_desc,is_on_sale,brand_id,cat_id,type_id,promote_price,promote_start_date,promote_end_date,is_new,is_hot,is_best,sort_num,is_floor';

	protected $_validate = array(
		array('cat_id','require','商品主分类不能为空',1),
		array('goods_name','require','商品名称不能为空',1),
		array('market_price','currency','市场价格必须是货币类型',1),
		array('shop_price','currency','本店价格必须是货币类型',1)
	);

	protected function _before_insert(&$data,$option){
		if($_FILES['logo']['error'] == 0){
			$res = uploadOne('logo','Goods',array(
					array(700,700),
					array(350,350),
					array(130,130),
					array(50,50),
				));
			$data['logo'] = $res['images'][0];
			$data['mbig_logo'] = $res['images'][1];
			$data['big_logo'] = $res['images'][2];
			$data['mid_logo'] = $res['images'][3];
			$data['sm_logo'] = $res['images'][4];
		}
		$data['addtime'] = date('Y-m-d H:i:s',time());
		$data['goods_desc'] = removeXSS($_POST['goods_desc']);
	}

	public function search($perPage  = 20){
		/********************搜索********************/
		$where = array();
		$gn = I('get.gn');
		if($gn)
			$where['t1.goods_name'] = array('like',"%$gn%");

		$fp = I('get.fp');
		$tp = I('get.tp');
		if($fp && $tp)
			$where['t1.shop_price'] = array('between',array($fp,$tp));
		elseif($fp)
			$where['t1.shop_price'] = array('egt',$fp);
		elseif($tp)
			$where['t1.shop_price'] = array('elt',$tp);
		//是否上架
		$ios = I('get.ios');
		if($ios)
			$where['t1.is_on_sale'] = array('eq',$ios);
		//添加时间
		$fa = I('get.fa');
		$ta = I('get.ta');
		if($fa && $ta)
			$where['t1.addtime'] = array('between',array($fa,$ta));
		elseif($fa)
			$where['t1.addtime'] = array('egt',$fa);
		elseif($ta)
			$where['t1.addtime'] = array('elt',$ta);
		
		//品牌
		$brandid = I('get.brand_id');
		if($brandid)
			$where['t1.brand_id'] = array('eq',$brandid);
		/**********排序***********/
		$odby = I('get.odby');
		$orderby = 't1.id';
		$orderway = 'desc';
		if($odby){
			if($odby == 'id_asc'){
				$orderway = 'asc';
			}elseif($odby == 'price_desc'){
				$orderby = 't1.shop_price';
			}elseif ($odby == 'price_asc') {
				$orderby = 't1.shop_price';
				$orderway = 'asc';
			}
		}
		//主分类,需要将该分类的子分类也查询出来
		$cat_id = I('get.cat_id');
		if($cat_id){
			//查询出该分类下所有的商品id
			$gids = $this->get_goodsid_by_catid($cat_id);
			if($gids)
				$where['t1.id'] = array('in',$gids);
			else
				$where['t1.id'] = 0;
		}
			

		
		//总记录数
		$count = $this->alias('t1')->where($where)->count();
		$pageObj = new \Think\Page($count,$perPage);
		$pageObj->setConfig('next','下一页');
		$pageObj->setConfig('prev','上一页');

		$pageString = $pageObj->show();

		/*************取数据**************/
		$data = $this->table('shopbill_goods')
		->alias('t1')
		->field('t1.*,t2.brand_name,t3.cat_name,group_concat(t5.cat_name separator "<br>") ext_cat_name')
		->join('left join __BRAND__ t2 on t1.brand_id=t2.id
				left join __CATEGORY__ t3 on t1.cat_id=t3.id
				left join __GOODS_CAT__ t4 on t1.id=t4.goods_id
				left join __CATEGORY__ t5 on t4.cat_id=t5.id')
		->where($where)
		->order($orderby.' '.$orderway)
		->limit($pageObj->firstRow.','.$pageObj->listRows)
		->group('t1.id')
		->select();
		//返回数据
		return array('data'=>$data,'page'=>$pageString);
	}
	protected function _before_update(&$data,$option){
		$id = $option['where']['id'];
		/***********修改logo************/
		if($_FILES['logo']['error'] == 0){
			$res = uploadOne('logo','Goods',array(
					array(700,700),
					array(350,350),
					array(130,130),
					array(50,50),
				));
			$data['logo'] = $res['images'][0];
			$data['mbig_logo'] = $res['images'][1];
			$data['big_logo'] = $res['images'][2];
			$data['mid_logo'] = $res['images'][3];
			$data['sm_logo'] = $res['images'][4];

			//原图路径（基于Goods目录）
			$oldlogo = $this->field('logo,mbig_logo,big_logo,mid_logo,sm_logo')->find($id);
			//拼凑完整的图片路径，并删除
			deleteImage($oldlogo);
		}

		/*******修改扩展分类******/
		$ext_cat_id = I('post.ext_cat_id');
		//先删除原有分类，再重新添加
		$goodscatmodel = D('goods_cat');
		$goodscatmodel->where(array('goods_id'=>array('eq',$id)))->delete();

		foreach($ext_cat_id as $v){
			if(!empty($v)){
				$goodscatmodel->add(array(
					'cat_id' => $v,
					'goods_id' => $id
				));
			}
		}
		

		
		/**********修改会员价格************/
		$mp = I('post.member_price');
		$mpmodel = D('member_price');
		$where['goods_id'] = array('eq',$id);
		$mpmodel->where($where)->delete();

		foreach($mp as $k => $v){
			$_v = (float)$v;
			if($_v > 0){
				$mpmodel->add(array(
				'price'=>$_v,
				'level_id'=>$k,
				'goods_id'=>$id				
				));
			}	
		}

		/***********修改相册图片************/
		if(isset($_FILES['pic'])){
			$pics = array();
			foreach ($_FILES['pic']['name'] as $k => $v)
			{
				$pics[] = array(
					'name' => $v,
					'type' => $_FILES['pic']['type'][$k],
					'tmp_name' => $_FILES['pic']['tmp_name'][$k],
					'error' => $_FILES['pic']['error'][$k],
					'size' => $_FILES['pic']['size'][$k],
				);
			}
			$_FILES = $pics;  // 把处理好的数组赋给$_FILES，因为uploadOne函数是到$_FILES中找图片
			$gpModel = D('goods_pic');
			// 循环每个上传
			foreach ($pics as $k => $v)
			{
				if($v['error'] == 0)
				{
					$ret = uploadOne($k, 'Goods', array(
						array(650, 650),
						array(350, 350),
						array(50, 50),
					));
					if($ret['ok'] == 1)
					{
						$gpModel->add(array(
							'pic' => $ret['images'][0],
							'big_pic' => $ret['images'][1],
							'mid_pic' => $ret['images'][2],
							'sm_pic' => $ret['images'][3],
							'goods_id' => $id,
						));
					}
				}
			}
		}

		/******修改商品属性*******/
		$attr_values = I('post.attr_values');
		$goods_attr_id = I('post.goods_attr_id');		
		$goodsattrmodel = D('goods_attr');

		//查出商品原来的类型
		$oldAttrType = I('post.old_attr_id');
		$newAttrType = I('post.type_id');
		if($oldAttrType !== $newAttrType){
			$goodsattrmodel->where(array('goods_id'=>array('eq',$id)))->delete();
			foreach($attr_values as $k => $v){
				foreach($v as $k1 => $v1){
					$goodsattrmodel->add(array(
						'goods_id' => $id,
						'attr_value' => $v1,
						'attr_id' => $k
					));
				}
			}
		}else{
			$i = 0;
			foreach($attr_values as $k => $v){
				foreach($v as $k1 => $v1){
					if($goods_attr_id[$i] == '' && $v1 !== ''){
						//新增的商品属性
						$goodsattrmodel->add(array(
							'goods_id' => $id,
							'attr_value' => $v1,
							'attr_id' => $k
						));
					}elseif($v1 !== ''){
						$goodsattrmodel->where('id='.$goods_attr_id[$i])
						->setField('attr_value',$v1);
					}
					++$i;
				}
			}
		}		
		
		//或者可以用replace into


		// 过滤这个goods_desc字段
		$data['goods_desc'] = removeXSS($_POST['goods_desc']);
	}
	protected function _before_delete($option){

		$id = $option['where']['id'];
		/***********删除会员价格***********/
		$mpModel = M('member_price');
		$mpModel->where(array('goods_id'=>$id))->delete();
		
		/******删除库存量******/
		$gnModel = D('goods_number');
		$gnModel->where('goods_id='.$id)->delete();
		/******删除logo******/
		$oldlogo = $this->field('logo,mbig_logo,big_logo,mid_logo,sm_logo')->find($id);
		deleteImage($oldlogo);	

		/***删除相册中的图片***/
		$gpmodel = D('goods_pic');
		//一个商品可能有多个相册图片
		$pics = $gpmodel->field('pic,sm_pic,mid_pic,big_pic')->where(array('goods_id'=>array('eq',$id)))->select();
		foreach($pics as $k => $v){
			deleteImage($v);
		}
		$gpmodel->where(array('goods_id'=>array('eq',$id)))->delete();

		/****删除商品扩展分类****/
		$goodscatmodel = D('goods_cat');
		$goodscatmodel->where(array('goods_id' => $id))->delete();

		/****删除商品属性****/
		$goodsattrmodel = D('goods_attr');
		$goodsattrmodel->where('goods_id='.$id)->delete();

	}
	protected function _after_insert($data,$options){
		/************添加商品扩展分类数据*************/
		$cat_id = I('post.ext_cat_id');
		$goodscatmodel = D('goods_cat');
		foreach($cat_id as $v){
			if(!empty($v)){
				$goodscatmodel->add(array(
					'cat_id' => $v,
					'goods_id' => $data['id']
				));
			}
		}

		 
		/************处理相册图片**************/
		if(isset($_FILES['pic'])){
			$pic = array();
            foreach($_FILES['pic']['name'] as $k => $v){
                
                $pics[] = array(
                    'name'=>$v,
                    'type'=>$_FILES['pic']['type'][$k],
                    'tmp_name'=>$_FILES['pic']['tmp_name'][$k],
                    'error'=>$_FILES['pic']['error'][$k],
                    'size'=>$_FILES['pic']['size'][$k],
                );
            }
            $_FILES = $pics;
            $gpmodel = D('goods_pic');

            foreach($pics as $k => $v){
            	if($v['error'] == 0){
            		$ret = uploadOne($k, 'Goods', array(
						array(650, 650),
						array(350, 350),
						array(50, 50),
					));
					if($ret['ok'] == 1)
					{
						$gpmodel->add(array(
							'pic' => $ret['images'][0],
							'big_pic' => $ret['images'][1],
							'mid_pic' => $ret['images'][2],
							'sm_pic' => $ret['images'][3],
							'goods_id' => $data['id'],
						));
					}
            	}
            }
		}

		/************处理会员价格**************/
		$mp = I('post.member_price');
		$mpmodel = D('member_price');

		foreach($mp as $k => $v){
			$_v = (float)$v;
			if($_v > 0){
				$mpmodel->add(array(
				'level_id' => $k,
				'price' => $_v,
				'goods_id' => $data['id'],
				));
			}
		}
		
		/*************处理商品属性表*************/
		$attr_values = I('post.attr_values');
		$goodsattrmodel = D('goods_attr');

		foreach($attr_values as $k => $v){
			$v = array_unique($v);
			foreach($v as $k1 => $v1){
				if($v1 != ''){
					$goodsattrmodel->add(array(
						'goods_id' => $data['id'],
						'attr_id' => $k,
						'attr_value' => $v1
					));
				}
			}
		}
		
	}
	//取出该分类下所有的商品id
	public function get_goodsid_by_catid($cat_id){
		$catmodel = D('Admin/category');
		$children = $catmodel->getChildren($cat_id);	//子取出分类
		//将当前主分类也放入数组中
		$children[] = $cat_id;

		/****根据当前商品分类的值，取出他们在主分类或扩展分类的有对应记录的商品的id***/
		//取出主分类下的商品id
		$gids = $this->field('id')->where(array(
			'cat_id' => array('in',$children)
		))->select();
		//取出扩展分类下的商品id
		$gcmodel = M('goods_cat');
		$gcids = $gcmodel->field('distinct goods_id id')->where(array(
			'cat_id' => array('in',$children)
		))->select();

		//合并两个数组
		if($gids && $gcids)
			$gids = array_merge($gids,$gcids);
		elseif($gcids)
			$gids = $gcids;
		
		//二维数组转一维数组
		$ids = array();
		foreach($gids as $v){
			$ids[] = $v['id'];
		}
		$ids = array_unique($ids);
		return $ids;
	}
	
	/***************前台*****************/
	public function getPromoteGoods($limit = 5){
		$today = date('Y-m-d H:i:s');

		$sql = "select id,goods_name,promote_price,mid_logo from shopbill_goods 
		where is_on_sale='是' and '".$today."' between promote_start_date and promote_end_date order by sort_num limit ".$limit;
		return M()->query($sql);
	}
	public function getRecGoods($type,$limit = 5){

		$sql = "select id,goods_name,shop_price,mid_logo from shopbill_goods 
		where is_on_sale='是' and $type='是' order by sort_num limit ".$limit;
		// echo $sql;die;
		return M()->query($sql);
	}
	public function getMemberPrice($id){
		if(session('m_level_id'))
			$levelId = session('m_level_id');	//会员id
		else
			$levelId = '';

		$sql = "select price from shopbill_member_price where goods_id='$id' and level_id='$levelId'";
		$row = M()->query($sql);
		$memPrice = $row[0]['price'];	//会员价

		$today = date('Y-m-d H:i:s');
		$sql = "select promote_price from shopbill_goods where id='$id' and '$today' between promote_start_date and promote_end_date";
		$row = M()->query($sql);
		$proPrice = $row[0]['promote_price'];	//促销价

		$sql = "select shop_price from shopbill_goods where id='$id'";
		$row = M()->query($sql);
		$shopPrice = $row[0]['shop_price'];		//本店价

		if($levelId){	//判断是否登录
			if($proPrice && $memPrice){
				return min($proPrice,$memPrice);	
			}elseif($memPrice){
				return $memPrice;
			}elseif($proPrice){
				return $proPrice;
			}else{
				return $shopPrice;
			}

		}else{
			if($proPrice){
				return min($proPrice,$shopPrice);
			}else{
				return $shopPrice;
			}
		}
	}
	public function getGoodNum(){
		$gid = I('post.goods_id');
		$gaid = I('post.goods_attr_id');
		$gnModel = D('goods_number');
		$gNum = $gnModel->field('goods_number')->where(array(
			'goods_id' => $gid,
			'goods_attr_id' => $gaid,
		))->find();
		echo $gNum['goods_number'];
	}

	public function catSearchRes($catId,$pageSize = 4){
		/*************** 搜索 *************************/
		// 根据分类ID搜索出这个分类下商品的ID
		$goodsId = $this->get_goodsid_by_catid($catId);
		$goodsId = implode(',', $goodsId);
		if($goodsId)
			$where .= " a.id in ($goodsId)";
		//品牌
		$brandId = I('get.brand_id');
		if($brandId){
			$brandId = str_replace(strstr($brandId,'-'),'',$brandId);
			$where .= " and a.brand_id = '$brandId'";
		}
		//价格
		$price = I('get.price');
		$arr = explode('-', $price);
		list($min,$max) = $arr;
		if($price){
			$where .= " and a.shop_price between $min and $max";
		} 
		/********商品属性搜索********/

		//若搜索条件中，有多个商品属性的条件，则将由这些条件查找出来的商品id放入到attrGoodsIds中
		$attrGoodsIds = array();

		foreach($_GET as $k => $v){
			//判断是否根据商品属性搜索
			if(strpos($k, 'attr_') === 0){
				$attrId = str_replace('attr_', '', $k);
				$attrValue = str_replace(strrchr($v, '-'),'',$v);
				//根据属性id和属性值查找对应的商品id
				$sql = "select goods_id from shopbill_goods_attr where attr_id = '$attrId' and attr_value = '$attrValue'";
				$gids = M()->query($sql);	//(array)$gids
				$_gids = array();
				foreach($gids as $k => $v){
					$_gids[] = $v['goods_id'];
				}
				
				if($_gids){
					//是否是第一次出现商品属性条件
					if(empty($attrGoodsIds)){
						//第一次
						$attrGoodsIds = $_gids;
					}else{
						//求两次搜索商品属性结果的交集
						$attrGoodsIds = array_intersect($attrGoodsIds, $_gids);
						if(empty($attrGoodsIds)){
							//商品不存在
							$where .= " and a.id = 0";
							break;
						}
					}
				}else{
					//商品不存在，清空结果集，并返回不存在的条件
					$attrGoodsIds = array();
					$where .= " and a.id = 0";
					break;
				}
			}

		}
		//循环完成，将得到的商品id作为属性查询条件
		if($attrGoodsIds){
			$attrGoodsIds = implode(',', $attrGoodsIds);
			$where .= " and a.id in ($attrGoodsIds)";
		}
		
		if(!$where)
			return false;
		
		/*************************分页******************************/
		$sql = "select count(1) num,group_concat(a.id) goods_id from shopbill_goods a where $where";
		$res = M()->query($sql);

		$count = $res[0]['num'];
		$data['goods_id'] = $res[0]['goods_id'];

		$Page = new \Think\Page($count,4);
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$data['page'] = $Page->show('sort_section');

		/*************************排序******************************/
		$oderbyField = 'sales';
		$orderbyWay = 'desc';
		$odby = I('get.odby');
		if($odby == 'price_asc'){
			$oderbyField = 'a.shop_price';
			$orderbyWay = 'asc';
		}elseif($odby == 'price_desc'){
			$oderbyField = 'a.shop_price';
		}elseif($odby == 'addtime'){
			$oderbyField = 'a.addtime';
		}


		//查询出符合搜索条件，并且算出已经卖出的商品的销量【这里需要商品订单表和订单表连接，查询出已经支付过的商品】
		$sql = "select a.id,a.goods_name,a.mid_logo,a.shop_price,SUM(b.goods_number) sales 
		from shopbill_goods a 
		left join shopbill_order_goods b on (a.id = b.goods_id 
		and b.order_id in(select id from shopbill_order where pay_status = '是')) 
		where $where group by a.id order by $oderbyField $orderbyWay limit $Page->firstRow,$Page->listRows";
		$data['data'] = M()->query($sql);
		return $data;

	}

	public function keySearchRes($keyword,$pageSize = 4){
		/***********搜索查询***********/
		//根据商品的【名称、描述、属性值】查询商品id
		$sql = "select distinct a.id from shopbill_goods a 
		left join shopbill_goods_attr b on a.id = b.goods_id 
		where goods_name like '%$keyword%' or goods_desc like '%$keyword%' or attr_value like '%$keyword%'";
		$res = M()->query($sql);
		foreach($res as $k => $v){
			$res1[] = $v['id'];
		}
		$gids = implode(',', $res1);
		if(empty($gids)){
			$where .= " a.id = 0";
		}else{
			$where .= " a.id in($gids)";
		}
		
		//品牌
		$brandId = I('get.brand_id');
		if($brandId){
			$brandId = substr(strstr($brandId, '-'),1);
			$where .= " and a.brand_id = '$brandId'";
		}
		//价格
		$price = I('get.price');
		$arr = explode('-', $price);
		list($min,$max) = $arr;
		if($price){
			$where .= " and a.shop_price between $min and $max";
		} 
		/********商品属性搜索********/

		//若搜索条件中，有多个商品属性的条件，则将由这些条件查找出来的商品id放入到attrGoodsIds中
		$attrGoodsIds = array();

		foreach($_GET as $k => $v){
			//判断是否根据商品属性搜索
			if(strpos($k, 'attr_') === 0){
				$attrId = str_replace('attr_', '', $k);
				$attrValue = str_replace(strrchr($v, '-'),'',$v);
				//根据属性id和属性值查找对应的商品id
				$sql = "select goods_id from shopbill_goods_attr where attr_id = '$attrId' and attr_value = '$attrValue'";
				$gids = M()->query($sql);	
				$_gids = array();
				foreach($gids as $k => $v){
					$_gids[] = $v['goods_id'];
				}
				
				if($_gids){
					//是否是第一次出现商品属性条件
					if(empty($attrGoodsIds)){
						//第一次
						$attrGoodsIds = $_gids;
					}else{
						//求两次搜索商品属性结果的交集
						$attrGoodsIds = array_intersect($attrGoodsIds, $_gids);
						if(empty($attrGoodsIds)){
							//商品不存在
							$where .= " and a.id = 0";
							break;
						}
					}
				}else{
					//商品不存在，清空结果集，并返回不存在的条件
					$attrGoodsIds = array();
					$where .= " and a.id = 0";
					break;
				}
			}

		}
		//循环完成，将得到的商品id作为属性查询条件
		if($attrGoodsIds){
			$attrGoodsIds = implode(',', $attrGoodsIds);
			$where .= " or a.id in ($attrGoodsIds)";
		}
		
		/*************************分页******************************/
		$sql = "select count(1) num,group_concat(a.id) goods_id from shopbill_goods a where $where";
		$res = M()->query($sql);

		$count = $res[0]['num'];
		$data['goods_id'] = $res[0]['goods_id'];

		$Page = new \Think\Page($count,4);
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$data['page'] = $Page->show('sort_section');

		/*************************排序******************************/
		$oderbyField = 'sales';
		$orderbyWay = 'desc';
		$odby = I('get.odby');
		if($odby == 'price_asc'){
			$oderbyField = 'a.shop_price';
			$orderbyWay = 'asc';
		}
		if($odby == 'price_desc'){
			$oderbyField = 'a.shop_price';
		}
		if($odby == 'addtime'){
			$oderbyField = 'a.addtime';
		}


		//查询出符合搜索条件，并且算出已经卖出的商品的销量【这里需要商品订单表和订单表连接，查询出已经支付过的商品】
		$sql = "select a.id,a.goods_name,a.mid_logo,a.shop_price,SUM(b.goods_number) sales 
		from shopbill_goods a 
		left join shopbill_order_goods b on (a.id = b.goods_id 
		and b.order_id in(select id from shopbill_order where pay_status = '是')) 
		where $where group by a.id order by $oderbyField $orderbyWay limit $Page->firstRow,$Page->listRows";
		$data['data'] = M()->query($sql);
		return $data;
	}
}