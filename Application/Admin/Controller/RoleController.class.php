<?php
namespace Admin\Controller;
class RoleController extends BaseController{
    public function add(){
    	if(IS_POST){
    		$model = D('Role');
    		if($data = $model->create(I('post.'), 1)){
    			if($id = $model->add()){
    				$this->success('添加成功！', U('lst?p='.I('get.p')));
    				exit;
    			}
    		}
    		$this->error($model->getError());
    	}
        //获取权限的数据
        $priModel = D('privilege');
        $priData = $priModel->getTree();

		$this->assign(array(
            'priData' => $priData,
			'_page_title' => '添加角色',
			'_page_btn_name' => '角色列表',
			'_page_btn_link' => U('lst'),
		));
		$this->display();
    }
    public function edit(){
    	$id = I('get.id');
    	if(IS_POST){
    		$model = D('Role');
    		if($model->create(I('post.'), 2)){
    			if($model->save() !== FALSE){
    				$this->success('修改成功！', U('lst', array('p' => I('get.p', 1))));
    				exit;
    			}
    		}
    		$this->error($model->getError());
    	}
        //获取角色名称
        $roleModel = M('role');
        $roleData = $roleModel->find($id);
        $this->assign('roleData', $roleData);

        //获取该角色的已有权限
    	$rpModel = M('role_pri');
    	$rpData = $rpModel->where(array('role_id' => $id))->select();
        $priIds = array();
        foreach($rpData as $k => $v){
            $priIds[] = $v['pri_id'];
        }
        $this->assign('priIds', $priIds);


        //获取所有权限
        $privModel = D('privilege');
        $priData = $privModel->getTree();
        $this->assign('priData', $priData);

		// 设置页面中的信息
		$this->assign(array(
			'_page_title' => '修改角色',
			'_page_btn_name' => '角色列表',
			'_page_btn_link' => U('lst'),
		));
		$this->display();
    }
    public function delete(){
    	$model = D('Role');
    	if($model->delete(I('get.id', 0)) !== FALSE){
    		$this->success('删除成功！', U('lst', array('p' => I('get.p', 1))));
    		exit;
    	}else{
    		$this->error($model->getError());
    	}
    }
    public function lst(){
    	$model = D('Role');
    	$data = $model->search();
    	$this->assign(array(
    		'data' => $data['data'],
    		'page' => $data['page'],
    	));

		$this->assign(array(
			'_page_title' => '角色列表',
			'_page_btn_name' => '添加角色',
			'_page_btn_link' => U('add'),
		));
    	$this->display();
    }
}