<?php
namespace Admin\Controller;
use Think\Controller;
class LoginController extends Controller{
	public function login(){
		if(IS_POST){
			$model = D('Admin');
			if($model->validate($model->_login_validate)->create()){
				if($model->login()){
					$this->success('登录成功!', U('Index/index'));
					exit;
				}
			}
			$this->error($model->getError());
		}
		$this->display();
	}
	public function logout(){
		$adminModel = D('Admin');
		$adminModel->logout();
		$this->redirect('login');	
	}
	public function buildVerify(){
		$config =    array(
		    'fontSize'    =>    15,    // 验证码字体大小
		    'length'      =>    1,     // 验证码位数
		    'useNoise'    =>    false, // 关闭验证码杂点
		    'imageW' => '150px',
		    'imageH' => '30px'
		);
		$Verify =     new \Think\Verify($config);
		$Verify->entry();
	}
} 