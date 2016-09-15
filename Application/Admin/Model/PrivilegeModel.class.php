<?php
namespace Admin\Model;
use Think\Model;
class PrivilegeModel extends Model 
{
	protected $insertFields = array('pri_name','module_name','controller_name','action_name','parent_id');
	protected $updateFields = array('id','pri_name','module_name','controller_name','action_name','parent_id');
	protected $_validate = array(
		array('pri_name', 'require', '权限名称不能为空！', 1, 'regex', 3),
		array('pri_name', '1,30', '权限名称的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('module_name', '1,30', '模块名称的值最长不能超过 30 个字符！', 2, 'length', 3),
		array('controller_name', '1,30', '控制器名称的值最长不能超过 30 个字符！', 2, 'length', 3),
		array('action_name', '1,30', '方法名称的值最长不能超过 30 个字符！', 2, 'length', 3),
		array('parent_id', 'number', '上级权限Id必须是一个整数！', 2, 'regex', 3),
	);
	public function getTree(){
		$data = $this->select();
		return $this->_reSort($data);
	}
	private function _reSort($data, $parent_id=0, $level=0, $isClear=TRUE){
		static $ret = array();
		if($isClear)
			$ret = array();
		foreach ($data as $k => $v){
			if($v['parent_id'] == $parent_id){
				$v['level'] = $level;
				$ret[] = $v;
				$this->_reSort($data, $v['id'], $level+1, FALSE);
			}
		}
		return $ret;
	}
	public function getChildren($id){
		$data = $this->select();
		return $this->_children($data, $id);
	}
	private function _children($data, $parent_id=0, $isClear=TRUE){
		static $ret = array();
		if($isClear)
			$ret = array();
		foreach ($data as $k => $v){
			if($v['parent_id'] == $parent_id){
				$ret[] = $v['id'];
				$this->_children($data, $v['id'], FALSE);
			}
		}
		return $ret;
	}
	//检查当前管理员是否有权限访问这个页面
	public function chkPri()
	{
		$adminId = session('id');
		// 如果是超级管理员直接返回 TRUE
		if($adminId == 1){
			return TRUE;
		}else{
			//用tp常量获取当前访问页面地址，拼接where条件
			$where = "module_name='".MODULE_NAME."' and controller_name='".CONTROLLER_NAME."' and action_name='".ACTION_NAME."'";
			//查询该管理员拥有的该权限
			$sql = "select count(1) num from shopbill_admin_role t1 
			left join shopbill_role_pri t2 on t1.role_id=t2.role_id 
			left join shopbill_privilege t3 on t2.pri_id=t3.id 
			where t1.admin_id='".$adminId."' and ".$where;
			$db = M();
			$pri = $db->query($sql);
			return ($pri[0]['num'] == 1);
		}	
	}
	public function getBtn(){
		$adminId = session('id');
		/*********取出该管理员所拥有的前两级权限**********/
	
		if($adminId == 1){
		    $sql = "select * from shopbill_privilege";
		}else{
		    $sql = "select distinct t3.id,t3.pri_name,t3.module_name,t3.controller_name,t3.action_name,t3.parent_id 
		    from shopbill_admin_role t1 
		    left join shopbill_role_pri t2 on t1.role_id=t2.role_id 
		    left join shopbill_privilege t3 on t2.pri_id=t3.id 
		    where t1.admin_id='".$adminId."'";        
		}
		$db = M();
		$pri = $db->query($sql);
		//取出前两级权限（二维数组转四维数组）
		$btn = array();
		foreach($pri as $k=> $v){
			if($v['parent_id'] == 0){
				foreach($pri as $k1 => $v1){
					if($v1['parent_id'] == $v['id']){
						$v['children'][] = $v1;
					}
				}
				$btn[] = $v;
			}
		}

		return $btn;
	}
	public function _before_delete($option)
	{
		// 先找出所有的子分类
		$children = $this->getChildren($option['where']['id']);
		// 如果有子分类都删除掉
		if($children)
		{
			$this->error = '有下级数据无法删除';
			return FALSE;
		}
	}
}