<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>管理中心 -  <?php echo $_page_title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="/Public/Admin/Styles/general.css" rel="stylesheet" type="text/css" />
<link href="/Public/Admin/Styles/main.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/Public/jquery.js"></script>
</head>
<body>
<h1>
	<?php if($_page_btn_name): ?>
    <span class="action-span"><a href="<?php echo $_page_btn_link; ?>"><?php echo $_page_btn_name; ?></a></span>
    <?php endif; ?>
    <span class="action-span1"><a href="#">管理中心</a></span>
    <span id="search_id" class="action-span1"> - <?php echo $_page_title; ?> </span>
    <div style="clear:both"></div>
</h1>

<!--  内容  -->

<style>
    li{list-style-type: none;background-color: #eee;margin: 5px;}
</style>
<div class="tab-div">
    <div id="tabbar-div">
        <p>
            <span class="tab-front">通用信息</span>
            <span class="tab-back">会员价格</span>
            <span class="tab-back">商品描述</span>
            <span class="tab-back">商品属性</span>
            <span class="tab-back">商品相册</span>
        </p>
    </div>
    <div id="tabbody-div">
        <form enctype="multipart/form-data" action="/Admin/Goods/edit/id/30.html" method="post">
        <input type="hidden" name="id" value="<?=I('get.id')?>" />
            <table width="90%" class="tab_table" align="center">
                <tr>
                    <td class="label">主分类：</td>
                    <td>
                        <select name="cat_id" id="cat_id">
                            <option value="">请选择</option>
                            <?php foreach($catdata as $v){ if($v['id'] == $data['cat_id']) $selected = 'selected="selected"'; else $selected = ''; ?>
                            <option <?=$selected?> value="<?=$v['id']?>"><?=str_repeat('-',4*$v['level']),$v['cat_name']?></option>
                            <?php }?>
                        </select>
                    </td>
                </tr>
                <tr>
                
                    <td class="label">扩展分类：<br><br>
                    <input onclick="var newli = $('#ext_cat_id').find('li:eq(0)').clone();newli.find('option:selected').removeAttr('selected');$('#ext_add_list').append(newli);" type="button" id="btn_add_cat" value="添加分类"><br><br>
                    <input onclick="$('#ext_add_list').find('li').last().remove();" type="button" id="btn_del_cat" value="删除分类"><br>
                    </td>
                    <td>
                    <ul id="ext_cat_id" style="padding-left:0px;">
                    <?php if($goodscatdata){?>
                		<?php foreach($goodscatdata as $goodscat){?>
                        <li>
                            <select name="ext_cat_id[]">
                                <option value="">请选择</option>
                                <?php foreach($catdata as $v){ if($v['id'] == $goodscat['cat_id']) $selected = 'selected="selected"'; else $selected = ''; ?>
                                <option <?=$selected?> value="<?=$v['id']?>"><?=str_repeat('-',4*$v['level']),$v['cat_name']?></option> 
                                <?php }?>  
                            </select>                          
                        </li>
                        <?php }?>
                     <?php }else{ ?>
                        <li>
                     		<select name="ext_cat_id[]">
                                <option value="">请选择</option>
                                <?php foreach($catdata as $v){?>
                                <option value="<?=$v['id']?>"><?=str_repeat('-',4*$v['level']),$v['cat_name']?></option> 
                                <?php }?>  
                            </select>
                        </li>
                      <?php }?>
                        <span id="ext_add_list"></span>
                    </ul> 
                    </td>
                </tr>
                <tr>
                    <td class="label">所属品牌：</td>
                    <td>
                        <?php buildSelect('brand','brand_id','id','brand_name',$data['brand_id']);?>
                        <!-- <select name="brand_id">
                            <option value="">请选择</option>
                            <?php foreach($branddata as $v){ if($v['id'] == $data['brand_id']) $select = 'selected="selected"'; else $select = ''; ?>
                            <option <?=$select?> value="<?=$v['id']?>"><?=$v['brand_name']?></option>
                            <?php }?>
                        </select> -->
                    </td>
                </tr>
                <tr>
                    <td class="label">商品名称：</td>
                    <td><input type="text" name="goods_name" size="60" value="<?=$data['goods_name']?>" />
                    <span class="require-field">*</span></td>
                </tr>
                <tr>
                    <td class="label">LOGO：</td>
                    <td>
                    <?php showImage($data['mid_logo']);?>
                    <input type="file" name="logo" size="60" /></td>
                </tr>
                
                <tr>
                    <td class="label">市场售价：</td>
                    <td>
                        <input type="text" name="market_price" value="<?=$data['market_price']?>" size="20" />
                        <span class="require-field">*</span>
                    </td>
                </tr>
                <tr>
                    <td class="label">本店售价：</td>
                    <td>
                        <input type="text" name="shop_price" size="20" value="<?=$data['shop_price']?>"/>
                        <span class="require-field">*</span>
                    </td>
                </tr>
                <tr>
                    <td class="label">是否上架：</td>
                    <td>
                        <input type="radio" name="is_on_sale" value="是" <?php if($data['is_on_sale']=='是') echo 'checked="checked"'?>/> 是
                        <input type="radio" name="is_on_sale" value="否" <?php if($data['is_on_sale']=='否') echo 'checked="checked"'?> /> 否
                    </td>
                </tr>
                <tr>
                    <td class="label">促销价格：</td>
                    <td>
                        ￥<input type="text" name="promote_price" value="<?=$data['promote_price']?>" size="15" /> 元
                        促销开始时间：<input type="text" name="promote_start_date" id="promote_start_date" value="<?=$data['promote_start_date']?>" /> 
                        促销结束时间：<input type="text" name="promote_end_date" id="promote_end_date" value="<?=$data['promote_end_date']?>" /> 
                    </td>
                </tr>
                <tr>
                    <td class="label">是否新品：</td>
                    <td>
                        <input type="radio" name="is_new" value="是" <?php if($data['is_new'] == '是') echo 'checked="checked"';?>/> 是
                        <input type="radio" name="is_new" value="否" <?php if($data['is_new'] == '否') echo 'checked="checked"';?>/> 否
                    </td>
                </tr>
                <tr>
                    <td class="label">是否热卖：</td>
                    <td>
                        <input type="radio" name="is_hot" value="是" <?php if($data['is_hot'] == '是') echo 'checked="checked"';?>/> 是
                        <input type="radio" name="is_hot" value="否" <?php if($data['is_hot'] == '否') echo 'checked="checked"';?>/> 否
                    </td>
                </tr>
                <tr>
                    <td class="label">是否精品：</td>
                    <td>
                        <input type="radio" name="is_best" value="是" <?php if($data['is_best'] == '是') echo 'checked="checked"';?>/> 是
                        <input type="radio" name="is_best" value="否" <?php if($data['is_best'] == '否') echo 'checked="checked"';?>/> 否
                    </td>
                </tr>
                <tr>
                    <td class="label">是否推荐楼层：</td>
                    <td>
                        <input type="radio" name="is_floor" value="是" <?php if($data['is_floor'] == '是') echo 'checked="checked"';?>/> 是
                        <input type="radio" name="is_floor" value="否" <?php if($data['is_floor'] == '否') echo 'checked="checked"';?> /> 否
                    </td>
                </tr>
                <tr>
                    <td class="label">排序：</td>
                    <td>
                        <input type="text" name="sort_num" value="<?=$data['sort_num']?>" /> 
                    </td>
                </tr>
            </table>
            <!-- 会员价格 -->
            <table style="display:none;" width="90%" class="tab_table" align="center">
                <tr>
                    <td class="label">会员价格：</td>
                    <td>
                       <!--  <?php foreach($mldata as $v){?>
                        <p><?=$v['level_name'];?>￥　<input type="text" size="10" name="member_price[<?=$v['id']?>]" 
                        value="<?php foreach($_mpdata as $k1 => $v1){ echo $k1 == $v['id'] ? $v1 : ''; }?>"></p>
                        <?php }?> -->
                        <?php foreach($mldata as $k => $v){?>
                        <p>
                        <?=$v['level_name']?>：￥　
                            <input type="text" size="20" name="member_price[<?=$v['id']?>]" value="<?php echo $_mpdata[$v['id']]?>" /> 元
                        </p>
                        <?php }?>
                    </td>
                </tr>
            </table>
            <!-- 商品描述 -->
            <table style="display:none;" width="90%" class="tab_table" align="center"> 
                <tr>
                    <td class="label">商品描述：</td>
                    <td>
                        <textarea id="goods_desc" name="goods_desc"><?=$data['goods_desc']?></textarea>
                    </td>
                </tr>
            </table>
             <!-- 商品属性 -->
            <table style="display:none;" width="90%" class="tab_table" align="center">
                <tr>
                    <td colspan="2" align="center">
                        商品类型：<?php buildSelect('type','type_id','id','type_name',$data['type_id'])?>
                    </td>
                    <input type="hidden" name="old_attr_id" value="<?=$data['type_id']?>">
                </tr>
                <tr>
                    <td width="40%"></td>
                    <td width="60%">
                        <ul id="attr_list">
                        <?php  $arr = array(); foreach($attrData as $k => $v){ if(in_array($v['id'],$arr)) $opt = '[-]'; else{ $opt = '[+]'; $arr[] = $v['id']; } ?>
                            <li>
                                <input type="hidden" name="goods_attr_id[]" value="<?=$v['goods_attr_id']?>">
                                <?php if($v['attr_type'] == '可选'){?>
                                    <a onclick="addNewAttr(this);" href="javascript:void(0)"><?=$opt?></a>
                                <?php }?>
                                <?=$v['attr_name']?>：
                                <?php if($v['attr_option_values'] !== ''){ $attrs = explode(',',$v['attr_option_values']); ?>
                                    <select name="attr_values[<?=$v['id']?>][]" id="">
                                        <option value="">请选择</option>
                                        <?php foreach($attrs as $k1 => $v1){ if($v1 == $v['attr_value']) $selected = 'selected="selected"'; else $selected = ''; ?>
                                            <option <?=$selected?> value="<?=$v1?>"><?=$v1?></option>
                                        <?php }?>
                                    </select>
                                <?php }else{ ?>
                                    <input type="text" name="attr_values[<?=$v['id']?>][]" value="<?=$v['attr_value']?>">
                                <?php }?>
                            </li>
                        <?php }?>



                       <!-- <?php  $attrid = array(); foreach($attributedata as $v){ if(in_array($v['attr_id'],$attrid)){ $opt = '-'; }else{ $opt = '+'; $attrid[] = $v['attr_id']; } ?>
                            <li>
                            <input type="hidden" name="goods_attr_id[]" value="<?=$v['id']?>">
                            <?php if($v['attr_type'] == '可选'){?>
                                <a onclick="addNewAttr(this);" href="#">[<?=$opt?>]</a>
                            <?php }?>
                            <?=$v['attr_name']?> : 
                            <?php if($v['attr_option_values']){ $attrs = explode(',',$v['attr_option_values']); ?>
                                <select name="attr_values[<?=$v['attr_id']?>][]" id="">
                                    <option value="">请选择</option>
                                    <?php foreach($attrs as $v1){ if($v1 == $v['attr_value']) $selected = 'selected="selected"'; else $selected = ''; ?>
                                        <option <?=$selected?> value="<?=$v1?>"><?=$v1?></option>
                                    <?php }?>
                                </select>
                            <?php }else{ ?>
                                <input type="text" name="attr_values[<?=$v['attr_id']?>][]" value="<?=$v['attr_value']?>">
                            <?php }?>
                            </li>
                        <?php }?> -->
                        </ul>
                    </td>
                </tr>
            </table>
            <!-- 商品相册 -->
            <table style="display:none;" width="90%" class="tab_table" align="center"> 
                <tr>
                    <td>
                    <input type="button" value="添加一张" id="btn_add_pic" />
                    <hr>
                    <ul id="ul_pic_list"></ul>
                    <hr>
                    <ul id="old_pic_list">
                    <?php foreach($gpdata as $v){?>
                        <li style="float:left;">
                        <?php showImage($v['mid_pic'],'150px');?>
                        <input pic_id="<?=$v['id']?>" class="btn_del_pic" type="button" value="删除" /><br>
                        </li>
                    <?php }?>
                    </ul>
                    </td>
                </tr>
            </table>

            <div class="button-div">
                <input type="submit" value=" 确定 " class="button"/>
                <input type="reset" value=" 重置 " class="button" />
            </div>
        </form>
    </div>
</div>

<!-- 引入时间插件 -->
<link href="/Public/datetimepicker/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" charset="utf-8" src="/Public/datetimepicker/jquery-ui-1.9.2.custom.min.js"></script>
<script type="text/javascript" charset="utf-8" src="/Public/datetimepicker/datepicker-zh_cn.js"></script>
<link rel="stylesheet" media="all" type="text/css" href="/Public/datetimepicker/time/jquery-ui-timepicker-addon.min.css" />
<script type="text/javascript" src="/Public/datetimepicker/time/jquery-ui-timepicker-addon.min.js"></script>
<script type="text/javascript" src="/Public/datetimepicker/time/i18n/jquery-ui-timepicker-addon-i18n.min.js"></script>
<script>
// 添加时间插件
$.timepicker.setDefaults($.timepicker.regional['zh-CN']);  // 设置使用中文 

$("#promote_start_date").datetimepicker();
$("#promote_end_date").datetimepicker();
</script>

<!--导入在线编辑器 -->
<link href="/Public/umeditor1_2_2-utf8-php/themes/default/css/umeditor.css" type="text/css" rel="stylesheet">
<script type="text/javascript" src="/Public/umeditor1_2_2-utf8-php/third-party/jquery.min.js"></script>
<script type="text/javascript" charset="utf-8" src="/Public/umeditor1_2_2-utf8-php/umeditor.config.js"></script>
<script type="text/javascript" charset="utf-8" src="/Public/umeditor1_2_2-utf8-php/umeditor.min.js"></script>
<script type="text/javascript" src="/Public/umeditor1_2_2-utf8-php/lang/zh-cn/zh-cn.js"></script>
<script>
UM.getEditor('goods_desc', {
	initialFrameWidth : "100%",
	initialFrameHeight : 350
});
$('#btn_add_pic').click(function(){
    var filearea = "<li><input type='file' name='pic[]'></li>";
    $('#ul_pic_list').append(filearea);
});
$('#tabbar-div p span').click(function(){
    var i = $(this).index();    //不传递参数，返回这个元素在同辈中的索引位置。  
    $('.tab_table').hide();
    $('.tab_table').eq(i).show();
    $('.tab-front').removeClass('tab-front').addClass('tab-back');
    $(this).removeClass('tab-back').addClass('tab-front');
});
$('.btn_del_pic').click(function(){
    if(confirm('确定要删除吗？')){
        var li = $(this).parent();
        var pic_id = $(this).attr('pic_id');
        var data = {pic_id:pic_id};

        // $.ajax({
        //     type:'GET',
        //     url:"<?php echo U('ajaxDelPic','',FALSE);?>/pic_id/"+pic_id,
        //     success:function(msg){
        //         li.remove();
        //     }
        // });

        $.post("<?=U('ajaxDelPic')?>",data,function(msg){
            li.remove();
            alert('删除成功');
        });  //没有数据，设为json会报错，不会运行回调函数,还是用$.ajax比较多

    }
});
</script>

<script>
    $("select[name='type_id']").change(function(){
        var type_id = $(this).val();

        if(type_id > 0){
            $.ajax({
                type:'post',
                data:{type_id:type_id},
                url: '<?=U('ajaxGetAttr')?>',
                dataType:'json',
                success:function(msg){

                        var li = '';
                        // 循环每个属性
                        $(msg).each(function(k,v){
                            li += '<li>';
                            //如果属性类型是可选值，就有一个+
                            if(v.attr_type == '可选'){
                                li += '<a onclick="addNewAttr(this)" href="#">[+]</a>';    
                            }                   
                            li += v.attr_name + ' : ';
                            //若属性有可选值就做下拉框，否则做文本框
                            if(v.attr_option_values !== ''){
                                //将属性id的值作为数组下标传递
                                li += '<select name="attr_values['+v.id+'][]"><option value="">请选择</option>';
                                //将可选值转化成数组
                                var attr_values = v.attr_option_values.split(',');

                                for(var i=0; i<attr_values.length; i++){
                                    li += '<option value="'+attr_values[i]+'">';
                                    li += attr_values[i]+'</option>';
                                }
                                li += '</select>';
                            }else{
                                li += '<input type="text" name="attr_values['+v.id+']" />';
                            }
                            
                            li += '</li>';
                        });
                        //将拼凑好的li放入页面中
                        $('#attr_list').html(li);
                }
            });
        }else{
            //选择了“请选择”
            $('#attr_list').html('');
        }
       /* var data = {type_id:type_id};
        $.post('<?=U('ajaxGetAttr')?>',data,function(msg){
            alert(msg);
        },'json');*/
    });
    function addNewAttr(a){
        var li = $(a).parent();

        if($(a).text() == '[+]'){
            var newli = li.clone();
            //去掉选中状态
            newli.find('option:selected').prop('selected',false);
            //修改时，去掉li中的隐藏域
            newli.find('input[name="goods_attr_id[]"]').val('');
            //改变操作符
            newli.find('a').text('[-]');
            li.after(newli);
        }else{
            var id = li.find('input[name="goods_attr_id[]"]').val();
            if(id){
                if(confirm('该属性和与其相关的库存量都会被删除，真的要删除吗？')){
                   var data = {id:id,goods_id:<?=$data['id']?>};
                    $.post("<?=U(ajaxDelAttr)?>",data,function(msg){
                        li.remove();    
                    });   
                }     
            }else{
                li.remove();
            }
            
        }
        

    }
</script>


























<div id="footer"> shopbill </div>
</body>
</html>