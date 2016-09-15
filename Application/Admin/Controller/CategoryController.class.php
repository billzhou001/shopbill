<?php
namespace Admin\Controller;
class CategoryController extends BaseController 
{
    public function lst(){
    	$model = D('category');
    	$data = $model->getTree();

    	$this->assign('data',$data);

		// 设置页面中的信息
		$this->assign(array(
			'_page_title' => '品牌分类',
			'_page_btn_name' => '添加分类',
			'_page_btn_link' => U('add'),
		));

    	$this->display();
    }
    public function delete(){
        $model = D('category');
        if(false !== $model->delete(I('get.id'))){
            $this->success('删除成功',U('lst'));
        }else{
            $this->error('删除失败,原因是：'.$model->getError());
        }
    }
    public function add(){
        $model = D('category');
        if(IS_POST){
            
            if($model->create(I('post.'),1)){
                if($model->add()){
                    $this->success('添加成功',U('lst'));
                    exit;
                }else{
                    $this->error($this->getError());
                }
            }
        }
        $catdata = $model->getTree();
        $this->assign(array(
            'catdata'=>$catdata,
            '_page_title' => '添加新分类',
            '_page_btn_name' => '分类列表',
            '_page_btn_link' => U('lst'),            
        ));
        $this->display();
    }
    public function edit(){
        $model = D('category');
        $id = I('get.id');
        if(IS_POST){
            
            if($model->create(I('post.'),2)){
                if($model->save() !== false){
                    $this->success('修改成功',U('lst'));
                    exit;
                }else{
                    $this->error($this->getError());
                }
            }
        }
        $data = $model->find($id);
        $catdata = $model->getTree();

        //获取当前分类的子分类id
        $children = $model->getChildren($id);

        $this->assign(array(
            'children'=>$children,
            'data'=>$data,
            'catdata'=>$catdata,
            '_page_title' => '添加新分类',
            '_page_btn_name' => '分类列表',
            '_page_btn_link' => U('lst'),            
        ));
        $this->display();
    }

    public function test(){
        $model = D('Category');
        $model->getNavData();
    }
}