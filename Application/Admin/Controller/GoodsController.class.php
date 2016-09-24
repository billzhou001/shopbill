<?php
namespace Admin\Controller;
class GoodsController extends BaseController {
    public function goods_number(){
       $id = I('get.id'); 
       //根据id取出该商品所有可选属性的值
       $goodsattrmodel = D('goods_attr');
       $goodsattrdata = $goodsattrmodel->alias('t1')
       ->field('t1.*,t2.attr_type,t2.attr_name')
       ->join('left join __ATTRIBUTE__ t2 on t1.attr_id=t2.id')
       ->where(array(
            't1.goods_id' => array('eq',$id),
            't2.attr_type' => array('eq','可选'),
            't1.attr_value' => array('neq','')
        ))
       ->select();

        //二维数组转三维数组
        $_gaData = array();
        foreach($goodsattrdata as $k => $v){
            $_gaData[$v['attr_name']][] = $v; //以属性名为下标对商品属性数据分类
            $_gaDataLst[$v['attr_name']][] = $v['attr_value']; //以属性名为下标对商品属性值标分类
        }

        //计算每组商品属性值，并放入一个数组中
        $arr1 = array();
        $res = array_shift($_gaDataLst);
        while ($arr2 = array_shift($_gaDataLst)) {
            $arr1 = $res;
            $res = array();
            foreach ($arr1 as $k => $v) {
                foreach ($arr2 as $k1 => $v1) {
                    $res[] = $v.','.$v1;
                }
            }
        }

        $gnModel = D('goods_number');

        if(IS_POST){
            $gnModel->where(array('goods_id' => $id))->delete();    //先将原有商品属性删除
            $gaids = null != I('post.goods_attr_id') ? I('post.goods_attr_id') : '';

            $gn = I('post.goods_number');

            $_i = 0;    //定义第_i个商品的商品属性id
            foreach($gn as $k => $v){
                //取出对应的商品属性id,先取出该商品属性个数
                $rate = count($_gaData);
                //再从商品属性id数组中取出对应的商品属性id
                $_gaid = array();
                for($i = 0; $i < $rate; $i++){
                    $_gaid[] = $gaids[$_i];
                    $_i++;
                }
                
                sort($_gaid,SORT_NUMERIC);
                $_gaid = implode(',', $_gaid);

                $gnModel->add(array(
                    'goods_id' => $id,
                    'goods_number' => $v,
                    'goods_attr_id' => $_gaid,
                ));
            }
            $this->success('设置成功',U('goods_number?id='.I('get.id')));
            die;
        }

        //取出这件商品已经设置过的库存量
        $gnData = $gnModel->where('goods_id='.$id)->select();

        $this->assign(array(
            '_gaData' => $_gaData,
            'gnData' => $gnData,
            'res' => $res,
            '_page_title' => '库存量',
            '_page_btn_name' => '商品列表',
            '_page_btn_link' => U('lst'),
        ));

        $this->display();

    }
    public function add(){
        if(IS_POST){
            set_time_limit(0);
            $model = D('goods');
            if($model->create(I('post.'),1)){
                if($model->add()){
                    $this->success('操作成功！','lst');
                    exit;
                }
            }
            $error = $model->getError();
            $this->error($error);
        }

        $brandmodel = D('brand');
        $branddata = $brandmodel->select();
        $mlmodel = D('member_level');
        $mldata = $mlmodel->select();

        //获取所有主分类
        $catmodel = D('category');
        $catdata = $catmodel->getTree();

        $this->assign(array(
            'catdata' => $catdata,
            'branddata' => $branddata,
            'mldata' => $mldata,
            '_page_title' => '添加新商品',
            '_page_btn_name' => '商品列表',
            '_page_btn_link' => U('lst'),
        ));

        $this->display();
    }
    public function lst(){
        $model = D('goods');
        
        $data = $model->search();
        $this->assign($data);

        $brandmodel = D('brand');
        $branddata = $brandmodel->select();

        $catmodel = D('category');
        $catdata = $catmodel->getTree();

        $this->assign(array(
            'catdata' => $catdata,
            'branddata' => $branddata,
            '_page_title' => '商品列表',
            '_page_btn_name' => '商品添加',
            '_page_btn_link' => U('add'),
        ));
        $this->display();
    }
    public function edit(){
        $id = I('get.id');
        $model = D('goods');
        if(IS_POST){
            if($data = $model->create(I('post.'),2)){
                if(false !== $model->save()){
                    $this->success('操作成功！',U('lst'));
                    exit;
                }
            }
            $error = $model->getError();
            $this->error($error);
        }

        //修改前的商品信息
        $data = $model->find($id);
        $this->assign('data',$data);

        $mlmodel = D('member_level');
        $mldata = $mlmodel->select();

        $mpmodel = D('member_price');   //会员价格
        $mpdata = $mpmodel->where(array('goods_id'=>array('eq',$id)))->select();
        $_mpdata = array();
        foreach($mpdata as $k=>$v){     //二位数组转一维数组方面插入会员价格表level_id => price
            $_mpdata[$v['level_id']] = $v['price'];
        }
        //取出相册中的图片
        $gpmodel = D('goods_pic');
        $gpdata = $gpmodel->field('id,mid_pic')->where(array('goods_id'=>array('eq',$id)))->select();

        $catmodel = D('category');
        $catdata = $catmodel->getTree();

        //获取当前分类的子分类id
        $children = $catmodel->getChildren($id);

        //获取商品扩展分类信息
        $goodscatmodel = D('goods_cat');
        $goodscatdata = $goodscatmodel->where(array('goods_id'=>array('eq',$id)))->select();

        //根据属性类型，商品id获取商品属性：属性名称，属性值。需要将该商品所属类型的所有属性都查出来

        $attributemodel = D('attribute');

        $attrData = $attributemodel->alias('t1')
        ->field('t1.*,t2.attr_value,t2.id goods_attr_id')
        ->join("left join __GOODS_ATTR__ t2 on (t1.id=t2.attr_id and t2.goods_id=$id)")
        ->where(array('type_id' => $data['type_id']))
        ->select();

        $this->assign(array(
            'attrData' => $attrData,            
            'goodscatdata' => $goodscatdata,            
            'catdata' => $catdata,            
            'mldata' => $mldata,            
            '_mpdata' => $_mpdata,            
            'gpdata' => $gpdata,            
            '_page_title' => '商品修改',
            '_page_btn_name' => '商品列表',
            '_page_btn_link' => U('lst'),
        ));
        $this->display();
    }
    public function delete(){
        $model = D('goods');
        if(false !== $model->delete(I('get.id'))){
            $this->success('删除成功',U('lst'));
        }else{
            $this->error('删除失败,原因是：'.$model->getError());
        }

    }
    public function ajaxDelPic(){
        $pic_id = I('post.pic_id');
        $gpmodel = D('goods_pic');
        $pic = $gpmodel->field('pic,sm_pic,mid_pic,big_pic')->find($pic_id);
        
        //从硬盘删除图片
        deleteImage($pic);
        //从数据库中删除这条记录
        $gpmodel->delete($pic_id);
    }
    public function ajaxGetAttr(){
        $type_id = I('post.type_id');
        if($type_id > 0){
            $attributemodel = D('attribute');
            $attrdata = $attributemodel->where('type_id='.$type_id)->select();
            echo json_encode($attrdata);
        }else{
            return false;
        }
        
    }
    public function ajaxDelAttr(){
        $gaid = I('post.id');
        $goods_id = I('post.goods_id');
        $gaModel = M('goods_attr');
        $gaModel->delete($gaid);

        //删除带有这个属性的库存量        
        $gnModel = D('goods_number');
        $gnModel->where(array(
            'goods_id' => array('exp',"=$goods_id and find_in_set($gaid,goods_attr_id)"),
        ))->delete();
    }
}