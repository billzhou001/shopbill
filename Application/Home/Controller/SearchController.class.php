<?php
namespace Home\Controller;
use Think\Controller;
class SearchController extends NavController{
	public function cat_search(){
		$catId = I('get.cat_id');

		// 取出商品和分页
		$goodsModel = D('Admin/goods');
		$data = $goodsModel->catSearchRes($catId);
		
		// 根据上面搜索出来的商品计算筛选条件
		$catModel = D('Admin/category');
		$searchFilter = $catModel->getSearchConditionByGoodsId($data['goods_id']);

		$catModel = D('Admin/category');
		$catPath = $catModel->parentPath($catId);

		//设置页面信息
        $this->assign(array(
        	'catPath' => $catPath,
        	'page' => $data['page'],
        	'data' => $data['data'],
        	'searchFilter' => $searchFilter,
            '_page_title' => '搜索页',
            '_page_keywords' => '搜索页',
            '_page_description' => '搜索页'
        ));
        $this->display();
	}
	public function key_search(){
		$keyword = I('get.keyword');
		// 取出商品和分页
		$goodsModel = D('Admin/goods');
		$data = $goodsModel->keySearchRes($keyword);
		
		// 根据上面搜索出来的商品计算筛选条件
		$catModel = D('Admin/category');
		if(!empty($data['goods_id']))
			$searchFilter = $catModel->getSearchConditionByGoodsId($data['goods_id']);
		else
			$searchFilter = '';

		$catModel = D('Admin/category');
		$catPath = $catModel->parentPath($catId);

		//设置页面信息
        $this->assign(array(
        	'catPath' => $catPath,
        	'page' => $data['page'],
        	'data' => $data['data'],
        	'searchFilter' => $searchFilter,
            '_page_title' => '搜索页',
            '_page_keywords' => '搜索页',
            '_page_description' => '搜索页'
        ));
        $this->display();
	}
}
