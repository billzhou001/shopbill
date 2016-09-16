<?php
namespace Home\Controller;
use Think\Controller;
class NavController extends Controller{
	public function __construct(){
		parent::__construct();
		//获取导航条中的数据
		$catModel = D("Admin/Category");
		$catData = $catModel->getNavData();
		$this->assign('catData',$catData);	
	}
}