<?php
namespace Home\Controller;
use Think\Controller;
class OrderController extends Controller {
    public function add(){
        $memberId = session('m_id');
        if(!$memberId){
            session('returnUrl',U('Order/add'));
            $this->error('您还未登录！请先登录',U('Member/login'));
        }
        $cartModel = M('cart');
        $cart = $cartModel->where(array('member_id'=>$memberId))->find();
        if(!$cart){
            session('returnUrl',U('Order/add'));
            $this->error('请选择商品',U('/'));
        }
        if(IS_POST){
            $odModel = D('Admin/order');
            if($odModel->create(I('post.',1))){
                if($orderId = $odModel->add()){
                    $this->success('下单成功！',U('Order/order_success?order_id='.$orderId));
                    die;
                }
            }
            $this->error('下单失败，原因是'.$odModel->getError());
            
        }
        //取出购物车中的信息
        $cartModel = D('Cart');
        $data = $cartModel->cartList();
        $this->assign('data',$data);

        $this->assign(array(
            '_page_title' => '首页',
            '_page_keywords' => '首页',
            '_page_description' => '首页'
        ));
        $this->display();
    }

    public function order_success(){

        $this->assign(array(
            '_show_nav' => 1,
            '_page_title' => '支付成功',
            '_page_keywords' => '支付成功',
            '_page_description' => '支付成功'
        ));
        $this->display();
    }
    
}