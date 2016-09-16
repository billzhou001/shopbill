<?php
namespace Admin\Model;
use Think\Model;
class AdminModel extends Model 
{
	//字段合法性过滤
	protected $insertFields = array('username','password','cpassword','captcha');
	protected $updateFields = array('id','username','password','cpassword','captcha');
	//在使用create创建数据对象的时候自动进行数据验证。
	protected $_validate = array(
		array('username', 'require', '用户名不能为空！', 1, 'regex', 3),
		array('username', '1,30', '用户名的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('password', 'require', '密码不能为空！', 1, 'regex', 1),
		array('cpassword', 'password', '两次密码不一致！', 1, 'confirm', 3),
		array('username', '', '用户名已经存在！', 1, 'unique', 3),

	);
	// 为登录的表单定义一个验证规则 ,动态创建自动验证规则
	public $_login_validate = array(
		array('username', 'require', '用户名不能为空！', 1),
		array('password', 'require', '密码不能为空！', 1),
		array('captcha', 'require', '验证码不能为空！', 1),
		array('captcha', 'check_verify', '验证码不正确！', 1, 'callback'),
	);
	// 验证验证码是否正确
	function check_verify($code, $id = ''){
	    $verify = new \Think\Verify();
	    return $verify->check($code, $id);
	}
	//在控制器中调用create方法接受表单数据，在模型里直接用$this获取就行了
	public function login(){
		$username = $this->data['username'];
		$password = $this->data['password'];
		$user = $this->where(array('username' => $username))->find();
		if($user){
			if($user['password'] == md5($password)){
				session('id',$user['id']);
				session('username',$user['username']);
				return true;
			}else{
				$this->error = '密码错误！';
				return false;
			}
		}else{
			$this->error = '登陆失败！';
			return false;
		}
	}

	public function logout(){
		session('id',null);
	}

	public function search($pageSize = 20){
		/**************************************** 搜索 ****************************************/
		$where = array();
		if($username = I('get.username'))
			$where['username'] = array('like', "%$username%");
		/************************************* 翻页 ****************************************/
		$count = $this->alias('a')->where($where)->count();
		$page = new \Think\Page($count, $pageSize);
		// 配置翻页的样式
		$page->setConfig('prev', '上一页');
		$page->setConfig('next', '下一页');
		$data['page'] = $page->show();
		/************************************** 取数据 ******************************************/
		$data['data'] = $this->alias('t1')
		->field('t1.*,group_concat(t3.role_name separator ";") role_name')
		->join('left join __ADMIN_ROLE__ t2 on t2.admin_id=t1.id
				left join __ROLE__ t3 on t3.id=t2.role_id')
		->where($where)
		->group('t1.id')
		->limit($page->firstRow.','.$page->listRows)
		->select();
		return $data;
	}
	// 添加前
	protected function _before_insert(&$data, $option){
		$data['password'] = md5($data['password']);

	}
	protected function _after_insert(&$data, $option){
		$role_id = I('post.role_id');
		$arModel = M('admin_role');

		foreach($role_id as $k => $v){
			$arModel->add(array(
				'admin_id' => $data['id'],
				'role_id' => $v
			));
		}
	}
	protected function _before_update(&$data, $option)
	{
		if($data['password'])
			$data['password'] = md5($data['password']);
		else
			unset($data['password']);

		//管理员角色中间表的数据插入，先删除后插入
		$arModel = M('admin_role');
		$arModel->where(array('admin_id' => $option['where']['id']))->delete();

		$role_id = I('post.role_id');

		foreach($role_id as $k => $v){
			$arModel->add(array(
				'admin_id' => $option['where']['id'],
				'role_id' => $v
			));
		}

	}
	protected function _before_delete($option){
		//删除admin_role表中的数据
		$id = $option['where']['id'];
		$arModel = M('admin_role');
		$arModel->where('admin_id='.$id)->delete();

		if($option['where']['id'] == 1){
			$this->error = '超级管理员不能删除！';
			return false;	
		}
	}
}