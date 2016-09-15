<?php
namespace Admin\Model;
use Think\Model;
class MemberModel extends Model 
{
	protected $insertFields = array('username','password','cpassword','captcha','face');
	protected $updateFields = array('id','username','password','cpassword','captcha','face');
	protected $_validate = array(
		array('username', 'require', '用户名不能为空！', 1, 'regex', 3),
		array('username', '1,30', '用户名的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('password', 'require', '密码不能为空！', 1, 'regex', 1),
		array('cpassword', 'password', '两次密码不一致！', 1, 'confirm', 3),
		array('username', '', '用户名已经存在！', 1, 'unique', 3),
		array('captcha', 'require', '验证码不能为空！', 1),
		array('captcha', 'check_verify', '验证码不正确！', 1, 'callback'),		

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
		// var_dump($this);die;
		$username = $this->username;
		$password = $this->password;
		$model = M('Member');

		$where['username'] = $username;
		$user = $model->where($where)->find();
		
		if($user){
			if(md5($password) == $user['password']){
				// 登录成功存session
				session('m_id', $user['id']);
				session('m_username', $user['username']);

				$config = C('IMAGE_CONFIG');
   		  		$viewPath = $config['viewPath'];
   		  		
				session('face',$viewPath.$user['face']);
				
				//计算会员等级id
				$sql = "select id from shopbill_member_level 
				where '".$user['jifen']."' between jifen_bottom and jifen_top";
				$level_id = M()->query($sql);
				session('m_level_id',$level_id[0]['id']);

				//将cookie中的数据移动到db中
				$cartModel = D('Home/cart');
				$cartModel->moveDataToDb();
				return TRUE;
			}else{
				$this->error = '密码错误！';
				return FALSE;
			}
		}else{
			$this->error = '用户名不存在！';
			return FALSE;
		}
	}
	public function logout(){
		session('m_username',null);
		session('m_id',null);
	}
	protected function _before_insert(&$data, $option)
	{
		$data['password'] = md5($data['password']);
		if($_FILES['face']['error'] == 0)
		{
			$upload = new \Think\Upload();// 实例化上传类
		    $upload->maxSize = 1024 * 1024 ; // 1M
		    $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		    $upload->rootPath = './Public/Uploads/'; // 设置附件上传根目录
		    $upload->savePath = 'Face/'; // 设置附件上传（子）目录
		    // 上传文件 
		    $info   =   $upload->upload();
		    if(!$info){
		    	$this->error = $upload->getError();
		        return FALSE;
		    }else{
		    	/**************** 生成缩略图 *****************/
		    	// 先拼成原图上的路径
		    	$face = $info['face']['savepath'] . $info['face']['savename'];
		    	$image = new \Think\Image(); 
		    	// 打开要生成缩略图的图片
		    	$image->open('./Public/Uploads/'.$face);
		    	// 生成缩略图
		    	$image->thumb(50, 50)->save('./Public/Uploads/'.$face);
		    	/**************** 把路径放到表单中 *****************/
		    	$data['face'] = $face;
		    }
		}

	}
	
	
}