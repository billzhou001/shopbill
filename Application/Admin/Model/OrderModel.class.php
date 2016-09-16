<?php
namespace Admin\Model;
use Think\Model;
class OrderModel extends Model 
{
	protected $insertFields = 'shr_name,shr_tel,shr_province,shr_city,shr_area,shr_address';
	protected $_validate = array(
		array('shr_name', 'require', '收货人姓名不能为空！', 1, 'regex', 3),
		array('shr_tel', 'require', '收货人联系方式不能为空！', 1, 'regex', 3),
		array('shr_province', 'require', '收货人省会不能为空！', 1, 'regex', 3),
		array('shr_city', 'require', '收货人城市不能为空！', 1, 'regex', 3),
		array('shr_area', 'require', '收货人地区不能为空！', 1, 'regex', 3),
		array('shr_address', 'require', '收货人地址不能为空！', 1, 'regex', 3),
	);
	public function search($pageSize = 20)
	{
		/**************************************** 搜索 ****************************************/
		$where = array(
			'member_id' => session('m_id')
		);
		
		/************************************* 翻页 ****************************************/
		$count = $this->alias('a')->where($where)->count();
		$page = new \Think\Page($count, $pageSize);
		// 配置翻页的样式
		$page->setConfig('prev', '上一页');
		$page->setConfig('next', '下一页');
		$data['page'] = $page->show();
		/************************************** 取数据 ******************************************/
		$data['orderData'] = $this->alias('a')
		->field('a.*,group_concat(distinct c.goods_name separator "<br/>") goods_name')
		->join('left join __ORDER_GOODS__ b on b.order_id = a.id
				left join __GOODS__ c on c.id = b.goods_id')
		->group('a.id')
		->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		return $data;
	}
	protected function _before_insert(&$data, $option){
		//判断是否登录、购物车中是否有数据、库存是否足够，并且补全表单信息
		$memberId = session('m_id');
		if(!$memberId){
			$this->error = '请登录！';
			return false;
		}
		$cartModel = D('Home/cart');
		$this->goods = $goods = $cartModel->cartList();
		if(!$goods){
			$this->error = '请选择商品！';
			return false;
		}
		$gnModel = M('goods_number');
		//计算总价
		$total = '';
		foreach($goods as $k=>$v){
			$gNum = $gnModel->field('goods_number')->where(array(
				'goods_id' => array('eq',$v['goods_id']),
				'goods_attr_id' => array('eq',$v['goods_attr_id']),
			))->find();
			if($v['goods_number'] > $gNum){
				$this->error = '库存不足！';
				return false;
			}
			$total += $v['goods_number']*$v['price'];
		}
		$data['total_price'] = $total;
		$data['member_id'] = $memberId;
		$data['addtime'] = time();

	}
	protected function _after_insert($data, $option){
		//插入订单商品表,并且【减库存！！】
		$ogModel = M('order_goods');
		$gnModel = M('goods_number');
		$goods = $this->goods;
		foreach($goods as $k=>$v){
			$ogModel->add(array(
				'order_id'=>$data['id'],
				'goods_id'=>$v['goods_id'],
				'goods_attr_id'=>$v['goods_attr_id'],
				'goods_number'=>$v['goods_number'],
				'price'=>$v['price'],
			));
			$gnModel->where(array(
				'goods_id' => array('eq',$v['goods_id']),
				'goods_attr_id' => array('eq',$v['goods_attr_id']),
			))->setDec('goods_number',$v['goods_number']);
		}
		//清空购物车
		$cartModel = D('Home/cart');
		$cartModel->clear();
		
	}	
}