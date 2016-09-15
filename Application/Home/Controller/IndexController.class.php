<?php
namespace Home\Controller;
class IndexController extends NavController {
    public function index(){
    	//获取疯狂促销商品
    	$goodsModel = D('Admin/Goods');
    	$goods1 = $goodsModel->getPromoteGoods();
    	$goods_new = $goodsModel->getRecGoods('is_new');
    	$goods_hot = $goodsModel->getRecGoods('is_hot');
    	$goods_best = $goodsModel->getRecGoods('is_best');

    	//楼层数据
    	$catModel = D('Admin/Category');
    	$floorData = $catModel->getFloorData();

		$this->assign(array(
			'goods1' => $goods1,
			'goods_new' => $goods_new,
			'goods_hot' => $goods_hot,
			'goods_best' => $goods_best,
			'floorData' => $floorData,
		));
	
    	//设置页面信息
    	$this->assign(array(
    		'_show_nav' => 1,
    		'_page_title' => '首页',
    		'_page_keywords' => '首页',
    		'_page_description' => '首页'
    	));
        $this->display();
    }
    public function goods(){
    	header('Content-type:text/html;charset=utf8');
    	$id = I('get.id');
    	$goodsModel = D('Admin/Goods');	//这里实例化的是Think\Model基类,M()方法中传入参数找数据表名
    	$goodsInfo = $goodsModel->find($id);
		if(!$goodsInfo){
			$this->error('页面不存在！',U('Home/Index/index'));
		}
		$catModel = D('Admin/Category');

		$catPath = $catModel->parentPath($goodsInfo['cat_id']);

		//取出会员价格
		$sql = "select t1.price,t2.level_name from shopbill_member_price t1
		left join shopbill_member_level t2 on t2.id = t1.level_id 
		where t1.goods_id = $id";
		$mpData = M()->query($sql);

		//取出相册中的图片
		$sql = "select * from shopbill_goods_pic where goods_id = $id";
		$gpData= M()->query($sql);
        
        //取出该商品所有属性
        $sql = "select t1.*,t2.attr_type,t2.attr_name from shopbill_goods_attr t1 
        left join shopbill_attribute t2 on t2.id = t1.attr_id 
        where t1.goods_id=$id";
        $attrInfo = M()->query($sql);

		//将可选属性和唯一属性分类存放到两个数组中,可选属性放存库中出现过的可选属性
		$uniAttr = $multiAttr = array();
		foreach($attrInfo as $k => $v){
			if($v['attr_type'] == '可选'){
				$multiAttr[$v['attr_name']][] = $v;
			}else{
                if($v['attr_value'] !== '')
				    $uniAttr[] = $v;
			}
		}

        //获取该商品的评论数
        $sql= "select count(1) num from shopbill_comment where goods_id='$id'";
        $res = M()->query($sql);
        $comNum = $res[0]['num'];
        //计算星级
        $sql = "select sum(star) total from shopbill_comment where goods_id='$id'";   
        $res = M()->query($sql);
        $total = $res[0]['total'];
        $haoping = ceil($total/$comNum);

		$config = C('IMAGE_CONFIG');
		$viewPath = $config['viewPath'];

		$this->assign(array(
			'goodsInfo' => $goodsInfo,
			'mpData' => $mpData,
			'gpData' => $gpData,
			'catPath' => $catPath,
			'uniAttr' => $uniAttr,
			'multiAttr' => $multiAttr,
			'viewPath' => $viewPath,
            'realPrice' => $realPrice,
            'comNum' => $comNum,
			'haoping' => $haoping,
		));
    	//设置页面信息
    	$this->assign(array(
    		'_show_nav' => 0,
    		'_page_title' => '商品详情页',
    		'_page_keywords' => '商品详情页',
    		'_page_description' => '商品详情页'
    	));
        $this->display();
    }
    public function displayHistory(){
        // $id = I('post.id');
        // $history = isset($_COOKIE['display_history']) ? unserialize($_COOKIE['display_history']) : array();
        // array_unshift($history, $id);
        // $history = array_unique($history);
        // $history = array_slice($history, 0, 6);
        // setcookie('display_history',serialize($history),time()+86400*7,'/');
        // $history = implode(',', $history);
        // $sql = "select id,goods_name,mid_logo from shopbill_goods 
        // where is_on_sale='是' and id in($history) order by field(id,$history)";
        // $res = M()->query($sql);
        // echo json_encode($res);




    	$id = I('post.id');
    	//取出浏览历史中的商品id（保存在cookie中）
    	$data = isset($_COOKIE['display_history']) ? unserialize($_COOKIE['display_history']) : array();
    	//将当前商品的id放到数组首位
    	array_unshift($data, $id);
    	//去重
    	$data = array_unique($data);
    	//只取6个商品
    	$data = array_slice($data, 0, 6);
    	//将最后的数组保存到cookie中
    	setcookie('display_history',serialize($data),time() + 7*86400,'/');
    	//根据商品id取出数据
    	$data = implode(',', $data);
    	$sql = "select id,goods_name,mid_logo from shopbill_goods where is_on_sale = '是' and id in ($data) 
    	order by field(id,$data)";
    	// echo $sql;die;
    	$history = M()->query($sql);
    	echo json_encode($history);
    }
    public function ajaxGetRealPrice(){
    	$gid = I('post.id');
    	$goodsModel = D('Admin/Goods');
		$realPrice = $goodsModel->getMemberPrice($gid);
		echo $realPrice;
    }
    public function test(){
    	$catModel = D('Admin/Category');
    	$catModel->getNavData();
    }
}