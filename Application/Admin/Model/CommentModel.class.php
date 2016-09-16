<?php
namespace Admin\Model;
use Think\Model;
class CommentModel extends Model {
	protected $insertFields = 'star,content,goods_id';
	
	protected $_validate = array(
		array('goods_id', 'require', '参数错误！', 1),
		array('star', '1,2,3,4,5', '分值只能是1-5之间的数字！', 1, 'in'),
		array('content', '1,200', '内容必须是1-200个字符！', 1, 'length'),
	);

	public function _before_insert(&$data,$option){
		$memberId = session('m_id');
		if(!$memberId){
			$this->error = '请先登录！';
			return false;
		}
		$data['member_id'] = session('m_id');
		$data['addtime'] = date('Y-m-d H:i:s');

		/*******将数据插入印象表********/
		$buyerYx = I('post.buyer_yx');
		$yxChk = I('post.yx_chk');
		$yxModel = M('yinxiang');
		if($yxChk){		
			foreach($yxChk as $k => $v){
				$sql = "update shopbill_yinxiang set yx_count=yx_count+1 where id='$v'";
				M()->execute($sql);
			}
		}
		
		if($buyerYx){	
			$buyerYx = str_replace('，', ',', $buyerYx);
			$buyerYx = explode(',', $buyerYx);
			foreach($buyerYx as $k => $v){
				$v = trim($v);
				if($v != ''){
					$has = $yxModel->where(array(
						'goods_id'=> $data['goods_id'],
						'yx_name' => $v
					))->find();			//判断该印象是否存在

					if($has){
						$sql = "update shopbill_yinxiang set yx_count=yx_count+1 where yx_name='$v' and goods_id='".$data['goods_id']."'";
						M()->execute($sql);
					}else{
						$yxModel->add(array(
						'goods_id' => $data['goods_id'],
						'yx_name' => $v,
						'yx_count' => 1,
						));
					}
				
				}
			}
		}
	}
	public function search($goodsId,$pageSize){
		//总评论数
		$sql = "select count(1) num from shopbill_comment where goods_id='$goodsId'";
		$res = M()->query($sql);

		$totalCom = $res[0]['num'];
		//计算总页数
		$pageCount = ceil($totalCom / $pageSize);
		//当前页
		$currentPage = max(1,(int)I('get.p'));
		//limit第一个参数
		$offset = ($currentPage - 1) * $pageSize;
		//请求第一页是，取出印象和好评数据
		if($currentPage == 1){
			$sql = "select * from shopbill_yinxiang where goods_id='$goodsId'";	//印象
			$yinxiang = M()->query($sql);
			$sql = "select star from shopbill_comment where goods_id='$goodsId'";	//好评
			$star_row = M()->query($sql);

			$hao = $zhong = $cha = 0;
			foreach($star_row as $v){
				if($v['star'] == 3){
					++$zhong;
				}elseif($v['star'] > 3){
					++$hao;
				}else{
					++$cha;
				}
			}
			$total = $hao + $zhong + $cha;
			$hao = round(($hao / $total) * 100, 1);
			$zhong = round(($zhong / $total) * 100, 1);
			$cha = round(($cha / $total) * 100, 1);
		}

		//取数据
		$sql = "select a.*,b.face,b.username,count(c.id) reply_count from shopbill_comment a 
		left join shopbill_member b on a.member_id=b.id 
		left join shopbill_comment_reply c on a.id=c.comment_id 
		where a.goods_id='$goodsId' group by a.id order by a.addtime desc limit $offset,$pageSize";
		$data = M()->query($sql);

		//将每个评论的回复取出，data就成了四维数组
		foreach($data as $k => &$v){
			$sql = "select a.content,a.addtime,b.username,b.face from shopbill_comment_reply a 
			left join shopbill_member b on a.member_id=b.id 
			where a.comment_id='".$v['id']."'";
			$v['reply'] = M()->query($sql);
		}
		$viewPath = C('IMAGE_CONFIG')['viewPath'];
		return array(
			'hao' => $hao,
			'zhong' => $zhong,
			'cha' => $cha,
			'yinxiang' => $yinxiang,
			'data' => $data,
			'pageCount' => $pageCount,
			'm_id' => session('m_id'),
			'viewPath' => $viewPath
		);

	}
}

