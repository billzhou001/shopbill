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


<div class="form-div">
    <form action="/Admin/Goods/lst" name="searchForm" method="get">
        <P>
            主分类名称：
            <select name="cat_id" id="">
                <option value="">请选择</option>
                <?php foreach($catdata as $v){ if(I('get.cat_id') == $v['id']) $selected = 'selected="selected"'; else $selected = ''; ?>
                <option <?=$selected?> value="<?=$v['id']?>"><?=str_repeat('-',4*$v['level']).$v['cat_name']?></option>
                <?php }?>
            </select>
        </P>
        <P>
            品　　牌：
            <!-- <?php buildSelect('brand','brand_id','id','brand_name',I('get.barnd_id'));?> -->
            <select name="brand_id">
                <option value="">请选择</option>
                <?php foreach($branddata as $v){ if($v['id'] == I('get.brand_id')) $selected = 'selected="selected"'; else $selected = '' ?>
                <option <?=$selected?> value="<?=$v['id']?>"><?=$v['brand_name']?></option>
                <?php }?>
            </select>
        </P>
        <P>
            商品名称：
            <input value="<?php echo I('get.gn'); ?>" type="text" name="gn" size="60" />
        </P>
        <P>
            价　　格：
            从<input value="<?php echo I('get.fp'); ?>" type="text" name="fp" size="8" />
            到<input value="<?php echo I('get.tp'); ?>" type="text" name="tp" size="8" />
        </P>
        <P>
            是否上架：
            <?php $ios = I('get.ios'); ?>
            <input onclick="this.parentNode.parentNode.submit();" type="radio" name="ios" value="" <?php if($ios == '') echo 'checked="checked"'; ?> /> 全部
            <input onclick="this.parentNode.parentNode.submit();" type="radio" name="ios" value="是" <?php if($ios == '是') echo 'checked="checked"'; ?> /> 上架
            <input onclick="this.parentNode.parentNode.submit();" type="radio" name="ios" value="否" <?php if($ios == '否') echo 'checked="checked"'; ?> /> 下架
        </P>
        <P>
            添加时间：
            从<input type="text" id="fa" name="fa" value="<?php echo I('get.fa'); ?>" size="20" />
            到<input type="text" id="ta" name="ta" value="<?php echo I('get.ta'); ?>" size="20" />
        </P>
        <p>
            排序方式：
            <?php $obdy = I('get.odby', 'id_desc'); ?>
            <input onclick="this.parentNode.parentNode.submit();" type="radio" name="odby" value="id_desc" <?php if($obdy == 'id_desc') echo 'checked="checked"'; ?> /> 以添加时间降序
            <input onclick="this.parentNode.parentNode.submit();" type="radio" name="odby" value="id_asc" <?php if($obdy == 'id_asc') echo 'checked="checked"'; ?> /> 以添加时间升序
            <input onclick="this.parentNode.parentNode.submit();" type="radio" name="odby" value="price_desc" <?php if($obdy == 'price_desc') echo 'checked="checked"'; ?> /> 以价格降序
            <input onclick="this.parentNode.parentNode.submit();" type="radio" name="odby" value="price_asc" <?php if($obdy == 'price_asc') echo 'checked="checked"';?>/> 以价格升序
        </p>
        <P>
            <input type="submit" value="搜索" />
        </P> 
    </form>
</div>

<!-- 商品列表 -->
<form method="post" action="/Admin/Goods/lst.html" name="listForm" >
    <div class="list-div" id="listDiv">
        <table cellpadding="3" cellspacing="1">
            <tr>
                <th>编号</th>
                <th>主分类</th>
                <th>扩展分类</th>
                <th>所属品牌</th>
                <th>商品名称</th>
                <th>logo</th>
                <th>市场价格</th>
                <th>本店价格</th>
                <th>上架</th>
                <th>添加时间</th>
                <th>操作</th>
            </tr>
            <?php foreach($data as $v){?>
            <tr class="tron">
                <td align="center"><?=$v['id']?></td>
                <td align="center"><?=$v['cat_name']?></td>
                <td align="center"><?=$v['ext_cat_name']?></td>
                <td align="center"><?=$v['brand_name']?></td>
                <td align="center"><?=$v['goods_name']?></td>
                <td align="center"><?php showImage($v['sm_logo']);?></td>
                <td align="center"><?=$v['market_price']?></td>
                <td align="center"><?=$v['shop_price']?></td>
                <td align="center"><?=$v['is_on_sale']?></td>
                <td align="center"><?=$v['addtime']?></td>
                <td align="center">
                    <a href="<?=U('goods_number?id='.$v['id'])?>">库存量</a>
                    <a href="<?=U('edit?id='.$v['id'])?>">修改</a>
                    <a onclick="return confirm('确认删除？')" href="<?=U('delete?id='.$v['id'])?>">删除</a>
                </td>
                
            </tr>
            <?php }?>
        </table>

    <!-- 分页开始 -->
        <table id="page-table" cellspacing="0">
            <tr>
                <td width="80%">&nbsp;</td>
                <td align="center" nowrap="true">
                    <?=$page?>
                </td>
            </tr>
        </table>
    <!-- 分页结束 -->
    </div>
    <!-- <input type="submit" value="搜索" /> -->
</form>

<script type="text/javascript" src="/Public/jquery.js"></script> 

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

$("#fa").datetimepicker();
$("#ta").datetimepicker();
</script>

<!-- 引入行高亮显示 -->
<script type="text/javascript" src="/Public/Admin/Js/tron.js"></script>




<div id="footer"> shopbill </div>
</body>
</html>