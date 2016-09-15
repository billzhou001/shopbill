<?php
function filterUrl($param,$param2 = ''){
	//获取当前执行脚本的文件地址【因为tp中没有？所以能够直接取出参数】
	$url = $_SERVER['PHP_SELF'];
	//匹配参数
	$reg = "/\/$param\/[^\/]+/";

	if(!empty($param2)){
		$reg2 = "/\/$param2\/[^\/]+/";
		$_url = preg_replace($reg2, '', $url);
		$newUrl = preg_replace($reg, '', $_url);
	}else{
		$newUrl = preg_replace($reg, '', $url);
	}
	
	return $newUrl;
}
//制作下拉框
function buildSelect($tableName, $selectName, $valueFieldName, $textFieldName, $selectedValue = '')
{
	$model = D($tableName);
	$data = $model->field("$valueFieldName,$textFieldName")->select();
	$select = "<select name='$selectName'><option value=''>请选择</option>";
	foreach ($data as $k => $v)
	{
		$value = $v[$valueFieldName];
		$text = $v[$textFieldName];
		if($selectedValue && $selectedValue==$value)
			$selected = 'selected="selected"';
		else 
			$selected = '';
		$select .= "<option $selected value='$value'>$text</option>";
	}
	$select .= '</select>';
	echo $select;
}
function deleteImage($images = array()){
	$savepath = C('IMAGE_CONFIG');
	foreach($images as $v){
		unlink($savepath['rootPath'].$v);
	}
}
function uploadOne($imgName, $dirName, $thumb = array())
{
	// 上传LOGO
	if(isset($_FILES[$imgName]) && $_FILES[$imgName]['error'] == 0)
	{
		$ic = C('IMAGE_CONFIG');
		$upload = new \Think\Upload(array(
			'rootPath' => $ic['rootPath'],
			'maxSize' => $ic['maxSize'],
			'exts' => $ic['exts'],
		));// 实例化上传类
		$upload->savePath = $dirName . '/'; // 图片二级目录的名称
		// 上传文件 
		// 上传时指定一个要上传的图片的名称，否则会把表单中所有的图片都处理，之后再想其他图片时就再找不到图片了
		$info   =   $upload->upload(array($imgName=>$_FILES[$imgName]));
		if(!$info)
		{
			return array(
				'ok' => 0,
				'error' => $upload->getError(),
			);
		}
		else
		{
			$ret['ok'] = 1;
		    $ret['images'][0] = $logoName = $info[$imgName]['savepath'] . $info[$imgName]['savename'];
		    // 判断是否生成缩略图
		    if($thumb)
		    {
		    	$image = new \Think\Image();
		    	// 循环生成缩略图
		    	foreach ($thumb as $k => $v)
		    	{
		    		$ret['images'][$k+1] = $info[$imgName]['savepath'] . 'thumb_'.$k.'_' .$info[$imgName]['savename'];
		    		// 打开要处理的图片
				    $image->open($ic['rootPath'].$logoName);
				    $image->thumb($v[0], $v[1])->save($ic['rootPath'].$ret['images'][$k+1]);
		    	}
		    }
		    return $ret;
		}
	}
}
function showImage($url, $width = '', $height = ''){
	$ic = C('IMAGE_CONFIG');
	if($width)
		$width = "width='$width'";
	if($height)
		$height = "height='$height'";
	echo "<img $width $height src='{$ic['viewPath']}$url' />";
}
function removeXSS($data){
	require_once './HtmlPurifier/HTMLPurifier.auto.php';
	$_clean_xss_config = HTMLPurifier_Config::createDefault();
	$_clean_xss_config->set('Core.Encoding', 'UTF-8');
	// 设置保留的标签
	$_clean_xss_config->set('HTML.Allowed','div,b,strong,i,em,a[href|title],ul,ol,li,p[style],br,span[style],img[width|height|alt|src]');
	$_clean_xss_config->set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align');
	$_clean_xss_config->set('HTML.TargetBlank', TRUE);
	$_clean_xss_obj = new HTMLPurifier($_clean_xss_config);
	// 执行过滤
	return $_clean_xss_obj->purify($data);
}
