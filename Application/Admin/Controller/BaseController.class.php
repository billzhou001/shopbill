<?php
namespace Admin\Controller;
use Think\Controller;
class BaseController extends Controller 
{
    public function __construct(){
        //必须先调用父类构造方法
        parent::__construct();
        //判断是否登录
        if(!session('id')){
            $this->error('请先登录！',U('Login/login'));
            die;
        }
        //判断后台的访问权限
        if(CONTROLLER_NAME == 'Index')
            return true;
        $priModel = D('Privilege');
        if(!$priModel->chkPri()){
            $this->error('无权访问！');
        }
    }
    public function _empty(){
        $this->error('无法访问',U('Index/index'));
    }
}