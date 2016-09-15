<?php
namespace Home\Controller;
use Think\Controller;
class MemberController extends Controller{
	public function login(){
		$model = D('Admin/Member');
		if(IS_POST){
			if($model->validate($model->_login_validate)->create()){
				if($model->login()){
					if($res = session('returnUrl')){
						session('returnUrl',null);
						$returnUrl = $res;
					}else{
						$returnUrl = U('/');
					}
					
					$this->success('登陆成功！',$returnUrl);
					die;
				}
			}
			$this->error($model->getError());
		}
		//设置页面信息
    	$this->assign(array(
    		'_page_title' => '登陆',
    		'_page_keywords' => '登陆',
    		'_page_description' => '登陆'
    	));
        $this->display();
	}
	public function register(){
		$model = D('Admin/Member');
		if(IS_POST){
			if($model->create(I('post.',1))){
				if($model->add()){
					$this->success('注册成功！',U('login'));
					die;
				}
			}
			$this->error($model->getError());
		}
    	$this->assign(array(
    		'_page_title' => '注册',
    		'_page_keywords' => '注册',
    		'_page_description' => '注册'
    	));
        $this->display();
	}
	public function logout(){
		$model = D('Admin/Member');
		$model->logout();
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
	public function ajaxChklogin(){
		if(session('m_id')){
			echo json_encode(1);
		}else{
			echo json_encode(0);
		}
	}
	public function ajaxGetGoodNum(){
		$gModel = D('Admin/Goods');
	    $gModel->getGoodNum();
	}


}