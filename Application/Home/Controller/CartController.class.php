<?php
namespace Home\Controller;
use Think\Controller;
class CartController extends Controller {
    public function add(){
        if (IS_POST) {
            $cartModel = D('Cart');
            if ($cartModel->create(I('post.'),1)) {
                if ($cartModel->add()) {
                    $this->success('成功加入购物车！',U('lst'));
                    die;
                }
            }
            $this->error('加入购物车失败，原因是'.$cartModel->getError());
        }
    }
    public function lst(){
        $cartModel = D('cart');
        $data = $cartModel->cartList();

        //设置页面信息
        $this->assign(array(
            'data' => $data,
            '_page_title' => '购物车页面',
            '_page_keywords' => '购物车页面',
            '_page_description' => '购物车页面'
        ));
        $this->display();
    }
    public function ajaxCartList(){
        $cartModel = D('cart');
        $data = $cartModel->cartList();
        if($data)
            echo json_encode($data);
        else
            echo json_encode(0);
    }
    public function ajaxDeleteCart(){
        $cartId = I('post.id');
        if($cartId){
            $cartModel = D('cart');
            $cartModel->delete($cartId);
        }else{
            $goodsinfo = I('post.goodsinfo');
            list($gid,$gaid) = explode('-', $goodsinfo);
            $arr = unserialize($_COOKIE['cart']);
            unset($arr[$gid.'-'.$gaid]);
            setcookie('cart',serialize($arr),time()*8*86400,'/');
        }
        
    }
    
}