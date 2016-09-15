<?php
namespace Admin\Model;
use Think\Model;
class CategoryModel extends Model{
	protected $insertFields = array('cat_name','parent_id','is_floor');
	protected $updateFields = array('id','cat_name','parent_id','is_floor');

	protected $_validate = array('cat_name', 'require', '分类名称不能为空！', 1, 'regex', 3);

	//找一个分类所有子分类的ID,用于删除分类的时候
	public function getChildren($catid){
		//取出所有分类
		$data = $this->select();
		//递归从所有的分类中挑出子分类的ID
		return $this->_getChildren($data,$catid,true);
	}

	//$isclear同一个脚本里第二次调用该函数时，清空之前数组里的值
	private function _getChildren($data,$catid,$isclear = false){
		static $res = array();
		if($isclear)
			$res = array();
		foreach($data as $v){
			if($v['parent_id'] == $catid){
				$res[] = $v['id'];
				$this->_getChildren($data,$v['id']);
			}
		}
		return $res;
	}

	//以树状结构获取所有的分类
	public function getTree(){
		$data = $this->select();
		return $this->_getTree($data);
	}

	private function _getTree($data,$parentid = 0,$level = 0){
		static $res = array();
		foreach($data as $v){
			if($v['parent_id'] == $parentid){
				$v['level'] = $level;
				$res[] = $v;
				$this->_getTree($data,$v['id'],$level+1);
			}
		}
		return $res;
	}

	public function _before_delete($option){
		$children = $this->getChildren($option['where']['id']);

		if($children){
			$where = implode(',', $children);
			$model = new \Think\Model;
			$model->table('__CATEGORY__')->delete($where);
		}
	}

	/******************前台方法******************/
	public function getNavData(){
		$catData = $this->select();
		$arr = array();
		foreach($catData as $k => $v){
			if($v['parent_id'] == 0){
				foreach($catData as $k1 => $v1){
					if($v1['parent_id'] == $v['id']){
						foreach($catData as $k2 => $v2){
							if($v2['parent_id'] == $v1['id']){
								$v1['children'][] = $v2;
							}
						}
						$v['children'][] = $v1;	
					}
				}
			$arr[] = $v;	
			}
		}
	return $arr;
		
	}
	public function getFloorData(){
		$floorData = S('floorData');

		if($floorData){
			return $floorData;
		}else{

			//获取推荐到楼层的顶级分类！！
			$where['parent_id'] = 0;
			$where['is_floor'] = '是';
			$res = $this->where($where)->select();

			$goodsModel = D('Admin/Goods');
			//获取每个楼层中的二级分类
			foreach($res as $k => $v){

				//取出楼层里未推荐的二级分类的数据，并保存到新添加的subCat字段里，注意这里的要么用&引用传递，要么$res[$k]['subCat']
				$where['parent_id'] = $v['id'];
				$where['is_floor'] = '否';
				$res[$k]['subCat'] = $this->where($where)->select();

				/***取出楼层里推荐的二级分类（这里拼成了一个五维数组）***/
				$res[$k]['recSubCat'] = $this->where(array(
					'parent_id' => array('eq',$v['id']),
					'is_floor' => array('eq','是'),
				))->select();

				/********取出推荐的二级分类下推荐的商品********/
				foreach($res[$k]['recSubCat'] as $k1 => &$v1){
					//取出该分类下所有的商品id
					$goodsIds = $goodsModel->get_goodsid_by_catid($v1['id']);
					$goodsIds = implode(',', $goodsIds);

					$sql = "select id,goods_name,shop_price,mid_logo from shopbill_goods 
					where is_on_sale = '是' and is_floor = '是' and id in($goodsIds) 
					order by sort_num limit 8";

					$v1['goods'] = M()->query($sql);
					

				}
				/**************取出这些分类下的商品的品牌***************/
				$gids = $goodsModel->get_goodsid_by_catid($v['id']);
				$gids = implode(',', $gids);

				//取出这些商品所属的品牌的logo
				$sql = "select distinct t2.logo from shopbill_goods t1 
				left join shopbill_brand t2 on t1.brand_id = t2.id
				where t1.id in($gids) and t1.brand_id!=0 
				order by t1.sort_num limit 8";
				// echo $sql;die;
				$brands = M()->query($sql);
				$res[$k]['brand'] = $brands;

			}
			//查询好数据库再放入缓存
			$floorData = S('floorData',$res,60);
			return $res;
		}
	}
	//根据分类id取出所有上级分类
	public function parentPath($catId){
		static $res = array();
		$catInfo = $this->find($catId);
		$res[] = $catInfo;
		if($catInfo['parent_id'] > 0){
			$this->parentPath($catInfo['parent_id']);
		}
		return $res;
	}
	
	public function getSearchConditionByGoodsId($_gids){
		if(!$_gids)
			return false;
		$ret = array();
		/***************** 品牌 ********************/
		$gModel = D('Admin/Goods');
		//取出这些商品所属的品牌
		$sql = "select distinct b.brand_name,b.id from shopbill_goods a 
		left join shopbill_brand b on a.brand_id = b.id 
		where a.id in ($_gids) and b.brand_name != ''";
		$ret['brands'] = M()->query($sql);
		/***************** 价格区间 ********************/
		//取出最高价和最低价
		$sql = "select MAX(shop_price) max_price,MIN(shop_price) min_price from shopbill_goods where id in ($_gids)";

		$priceInfo = M()->query($sql);
		//定义最高价和最低价的区间
		$priceDiff = $priceInfo[0]['max_price'] - $priceInfo[0]['min_price'];
		//取出商品数量
		$arr_gids = explode(',', $_gids);
		$goodsCount = count($arr_gids);
		//定义分段数量
		$sectionCount = 0;

		if($goodsCount > 2){
			if($priceDiff < 100){
				$sectionCount = 2;
			}elseif($priceDiff < 1000){
				$sectionCount = 4;
			}elseif($priceDiff < 10000){
				$sectionCount = 6;
			}else{
				$sectionCount = 7;
			}
			
			$priceOffset = ceil($priceDiff / $sectionCount);
			$minPrice = $priceInfo[0]['min_price'];

			$price = array();
			for($i = 0;$i < $sectionCount;$i++){
				$maxPrice = $minPrice+$priceOffset;
				if($maxPrice > $priceInfo[0]['max_price']){
					$maxPrice = $priceInfo[0]['max_price'];
				}
				$price[] = $minPrice.'-'.$maxPrice;
				$minPrice = $minPrice + $priceOffset + 1;
			}

		}
		$ret['price'] = $price;

		/***************** 商品属性 ********************/
		$sql = "select group_concat(distinct a.attr_value) attr_value,b.attr_name,b.id from shopbill_goods_attr a 
		left join shopbill_attribute b on b.id=a.attr_id 
		where goods_id in ($_gids) group by b.id";
		$ret['goodsAttr'] = M()->query($sql);

		return $ret;
	}
}


















			
