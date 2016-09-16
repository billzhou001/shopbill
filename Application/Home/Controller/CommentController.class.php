<?php
namespace Home\Controller;
use Think\Controller;
class CommentController extends Controller{
	public function add(){
		if(IS_POST){
			$comModel = D('Admin/comment');
			if($comModel->create(I('post.'),1)){
				if($id = $comModel->add()){
					$this->success(array(
						'id' => $id,
						'username' => session('m_username'),
						'face' => session('face'),
						'content' => I('post.content'),
						'addtime' => date('Y-m-d H:i:s'),
						'star' => I('post.star')
					),'',true);
				}
			}
			$this->error($comModel->getError(),'',true);
		}	
	}
	public function ajaxGetComment(){
		$goodsId = I('get.id');
		
		$comModel = D('Admin/comment');
		$data = $comModel->search($goodsId,5);
		echo json_encode($data);
	}
	public function reply(){
		
		if(IS_POST){
			$crModel = D('Admin/CommentReply');
			if($crModel->create(I('post.'),1)){
				if($crModel->add()){
					$this->success(array(
						'username' => session('m_username'),
						'face' => session('face'),
						'content' => I('post.content'),
						'addtime' => date('Y-m-d H:i:s'),
					),'',true);
				}
			}
			$this->error($crModel->getError());
		}	
	}
	public function ajaxAddUseful(){
		$comModel = D('Admin/comment');
		$res = $comModel->where(array('id'=>I('get.comment_id')))->setInc('click_count');
		if($res !== false)
			echo 1;
	}

}