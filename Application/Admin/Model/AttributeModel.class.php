<?php
namespace Admin\Model;
use Think\Model;
class AttributeModel extends Model 
{
	protected $insertFields = array('attr_name','attr_type','attr_option_values','type_id');
	protected $updateFields = array('id','attr_name','attr_type','attr_option_values','type_id');
	protected $_validate = array(
		array('attr_name', 'require', '属性名称不能为空！', 1, 'regex', 3),
		array('attr_name', '1,30', '属性名称的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('attr_type', 'require', '属性类型不能为空！', 1, 'regex', 3),
		array('attr_type', '唯一,可选', "属性类型的值只能是在 '唯一,可选' 中的一个值！", 1, 'in', 3),
		array('attr_option_values', '1,300', '属性可选值的值最长不能超过 300 个字符！', 2, 'length', 3),
		array('type_id', 'require', '所属类型Id不能为空！', 1, 'regex', 3),
		array('type_id', 'number', '所属类型Id必须是一个整数！', 1, 'regex', 3),
	);
	public function search($pageSize = 20)
	{
		/**************************************** 搜索 ****************************************/
		$where = array();
		if($attr_name = I('get.attr_name'))
			$where['attr_name'] = array('like', "%$attr_name%");
		$attr_type = I('get.attr_type');
		if($attr_type != '' && $attr_type != '-1')
			$where['attr_type'] = array('eq', $attr_type);
		if($type_id = I('get.type_id'))
			$where['type_id'] = array('eq', $type_id);
		/************************************* 翻页 ****************************************/
		$count = $this->alias('a')->where($where)->count();
		$page = new \Think\Page($count, $pageSize);
		// 配置翻页的样式
		$page->setConfig('prev', '上一页');
		$page->setConfig('next', '下一页');
		$data['page'] = $page->show();
		/************************************** 取数据 ******************************************/
		$data['data'] = $this->alias('a')
		->field('a.*,b.type_name')
		->join('left join __TYPE__ b on a.type_id=b.id')
		->where($where)->group('a.id')->limit($page->firstRow.','.$page->listRows)->select();
		return $data;
	}
	protected function _before_insert(&$data, $option)
	{
		$data['attr_option_values'] = str_replace('，', ',', $data['attr_option_values']);
	}
	protected function _before_update(&$data, $option)
	{
		$id = I('post.id');
		$data['attr_option_values'] = str_replace('，', ',', $data['attr_option_values']);
		//将含有该属性的商品属性里的属性类型都改为修改之后的类型
		$sql = "update shopbill_attribute set attr_type='".$data['attr_type']."' where id='".$id."'";
		M()->execute($sql);
		//查询出所有含有该属性的goods_attr的id
		$sql = "select goods_id from shopbill_goods_attr where attr_id='$id' group by goods_id";
		$res = M()->query($sql);

		foreach($res as $k=>$v){
			$newRow = M()->query("select * from shopbill_goods_attr where goods_id='".$v['goods_id']."' and attr_id='$id' group by goods_id");
			$priId = $newRow[0]['id'];
			$gaValue = $newRow[0]['attr_value'];
			$attrId = $newRow[0]['attr_id'];
			$gaid = $newRow[0]['goods_id'];
			M()->execute("delete from shopbill_goods_attr where goods_id='".$v['goods_id']."' and attr_id='$id'");
			M()->execute("insert into shopbill_goods_attr values('$priId','$gaValue','$attrId','$gaid')");
		}
	}
}