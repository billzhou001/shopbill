<?php
return array(
	//'配置项'=>'配置值'
	'HTML_CACHE_ON'     =>    false, // 开启静态缓存
	'HTML_CACHE_TIME'   =>    86400,   // 全局静态缓存有效期（秒）
	'HTML_FILE_SUFFIX'  =>    '.shtml', // 设置静态缓存文件后缀
	'HTML_CACHE_RULES'  =>     array(  
		'index:index' => array('index',86400),
		'index:goods' => array('goods_{id}',86400),
	),

);