<?php
namespace Admin\Model;
use Think\Model;
class RoleModel extends Model 
{
	protected $insertFields = array('role_name');
	protected $updateFields = array('id','role_name');
	protected $_validate = array(
		array('role_name', 'require', '角色名称不能为空！', 1, 'regex', 3),
		array('role_name', '1,30', '角色名称的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('role_name', '', '角色名称已经存在！', 1, 'unique', 1),
	);
	public function search($pageSize = 20)
	{
		/**************************************** 搜索 ****************************************/
		$where = array();
		/************************************* 翻页 ****************************************/
		$count = $this->alias('a')->where($where)->count();
		$page = new \Think\Page($count, $pageSize);
		// 配置翻页的样式
		$page->setConfig('prev', '上一页');
		$page->setConfig('next', '下一页');
		$data['page'] = $page->show();
		    
		//查询出该角色所拥有的所有权限名称
		$data['data'] = $this->alias('t1')
		->field("t1.*,group_concat(t3.pri_name separator ';') pri_name")
		->join('left join __ROLE_PRI__ t2 on t1.id=t2.role_id 
				left join __PRIVILEGE__ t3 on t2.pri_id=t3.id')
		->where($where)
		->group('t1.id')
		->limit($page->firstRow.','.$page->listRows)
		->select();
		return $data;
	}
	protected function _after_insert(&$data, $option){
		$priId = I('post.pri_id');
		$rpModel = D('role_pri');

		foreach($priId as $k => $v){
			$rpModel->add(array(
				'pri_id' => $v,
				'role_id' => $data['id']
			));
		}
	}
	protected function _before_update(&$data, $option){
		$priId = I('post.pri_id');
		$rpModel = D('role_pri');

		//遇到既有修改又有添加的情况，就先删除在添加
		$rpModel->where('role_id='.$option['where']['id'])->delete();

		foreach($priId as $k => $v){
			$rpModel->add(array(
				'pri_id' => $v,
				'role_id' => $option['where']['id']
			));
		}		
	}
	protected function _before_delete($option){
		$rpModel = M('role_pri');
		//删除该角色在角色权限表中的数据
		$where['role_id'] = $option['where']['id'];
		$rpModel->where($where)->delete();

		$arModel = M('admin_role');
		//删除该角色在角色管理员表中的数据
		$where['role_id'] = $option['where']['id'];
		$rpModel->where($where)->delete();	

		if(is_array($option['where']['id']))
		{
			$this->error = '不支持批量删除';
			return FALSE;
		}
	}
}