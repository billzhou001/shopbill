<?php
namespace Home\Controller;
use Think\Controller;
class MyController extends Controller{
	public function __construct(){
		parent::__construct();
		if(!session('m_id')){
			session('returnUrl',U('My/'.ACTION_NAME));
			// echo 1;die;
			redirect(U('Member/login'));
		}	
	}
	public function order(){
		$orderModel = D('Admin/order');
		$data = $orderModel->search();

		//设置页面信息
        $this->assign(array(
            'data' => $data,
            '_page_title' => '订单页',
        ));
        $this->display();
	}

}