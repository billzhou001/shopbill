<?php
namespace Admin\Controller;
use Think\Controller;
class EmptyController extends Controller{
	public function _empty(){
		$this->error('页面不存在。。。',U('Login/login'));	
	}
}