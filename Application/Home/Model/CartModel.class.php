<?php
namespace Home\Model;
use Think\Model;
class CartModel extends Model 
{
	protected $insertFields = 'goods_id,goods_attr_id,goods_number';
	protected $_validate = array(
		array('goods_id', 'require', '必须选择商品', 1),
		array('goods_number', 'chkGoodsNumber', '库存量不足！', 1, 'callback'),
	);
	// 检查库存量
	public function chkGoodsNumber($goodsNumber){
		// 选择的商品属性id
		$gaid = I('post.goods_attr_id');
		sort($gaid, SORT_NUMERIC);
		$gaid = (string)implode(',', $gaid);

		$goods_id = I('post.goods_id');
		// 取出库存量
		$sql = "select goods_number from shopbill_goods_number where goods_id = '$goods_id' and goods_attr_id = '$gaid'";
		$res = M()->query($sql);
		$gnNum = $res[0]['goods_number'];
		// echo $sql;
		// 返回库存量是否够
		return ($gnNum >= $goodsNumber);
	}
	// 重写父类的add方法：判断如果没有登录是存COOKIE，否则存数据库
	public function add(){
		$memberId = session('m_id');
		//接收数据
		$gid = $this->goods_id;
		$gaid = $this->goods_attr_id;
		$gNum = $this->goods_number;
		//处理商品属性id
		sort($gaid,SORT_NUMERIC);
		$gaid = implode(',', $gaid);

		if($memberId){
			//存数据库,先判断该改频是否存在
			$sql = "select * from shopbill_cart where goods_id='$gid' and goods_attr_id='$gaid' and member_id='$memberId'";
			$row = M()->query($sql);
			$cartId = $row[0]['id'];
			if($cartId){
				$sql = "update shopbill_cart set goods_number=goods_number+'$gNum' where id='$cartId'";
				$res = M()->execute($sql);
			}else{
				parent::add(array(
					'goods_id' => $gid,
					'goods_attr_id' => $gaid,
					'goods_number' => $gNum,
					'member_id' => $memberId,
				));
			}
		}else{
			//未登录存cookie
			$cartData = isset($_COOKIE['cart']) ? unserialize($_COOKIE['cart']) : array();
			if($cartData[$gid.'-'.$gaid])
				$cartData[$gid.'-'.$gaid] += "$gNum";	//+=
			else
				$cartData[$gid.'-'.$gaid] = "$gNum";
			setcookie('cart',serialize($cartData),time()+30*86400,'/');
		}
		return true;
	}

	public function moveDataToDb(){
		$memberId = session('m_id');

		if($data = unserialize($_COOKIE['cart'])){

			foreach($data as $k => $v){
				$arr = explode('-', $k);
				$gid = $arr[0];
				$gaid = $arr[1];
				//判断该商品是否已经在该会员的购物车中
				$sql = "select * from shopbill_cart where goods_id='$gid' and goods_attr_id='$gaid' and member_id='$memberId'";
				$row = M()->query($sql);
				$has = $row[0]['id'];

				if($has){
					$sql = "update shopbill_cart set goods_number=goods_number+$v where id='$has'";
					M()->execute($sql);
				}else{
					parent::add(array(
						'goods_id' => $gid,
						'goods_attr_id' => $gaid,
						'goods_number' => $v,
						'member_id' => $memberId,
					));
				}	
			}
		}
		setcookie('cart','',time()-1,'/');
	}

	// 取出购物车页面商品信息
	public function cartList(){
		$memberId = session('m_id');
		//判断是否登陆，并取出商品信息
		if($memberId){
			$cartData = $this->where(array('member_id'=>$memberId))->select();
		}else{
			if($cart = unserialize($_COOKIE['cart'])){
				foreach($cart as $k=>$v){
					$k = explode('-', $k);
					$data['goods_id'] = $k[0];
					$data['goods_attr_id'] = $k[1];
					$data['goods_number'] = $v;
					$cartData[] = $data;
				}
			}
		} 
		//取出详细信息mid_logo,goods_name,attr_name,price
		foreach($cartData as $k=>&$v){
			//取mid_logo,goods_name
			$sql = "select mid_logo,goods_name from shopbill_goods 
			where id='".$v['goods_id']."' group by goods_name";
			$res = M()->query($sql);
			$v['mid_logo'] = $res[0]['mid_logo'];
			$v['goods_name'] = $res[0]['goods_name'];
			//取出attr_name
			$gaArr = explode(',', $v['goods_attr_id']);
			for($i=0;$i<count($gaArr);$i++){
				$sql = "select b.attr_name,a.attr_value from shopbill_goods_attr a 
				left join shopbill_attribute b on a.attr_id=b.id 
				where a.id='".$gaArr[$i]."'";
				$res = M()->query($sql);
				$v['attr_name'][] = $res[0]['attr_name'];
				$v['attr_value'][] = $res[0]['attr_value'];
			}			
			// 取出price
			$gModel = D('Admin/goods');
			$v['price'] = $gModel->getMemberPrice($v['goods_id']);	

		}
		return $cartData;
	}

	public function clear(){
		$this->where(array('member_id' => session('m_id')))->delete();
	}
	
}














