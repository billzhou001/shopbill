<?php
return array(
	//'配置项'=>'配置值'
	'DB_TYPE'               =>  'mysqli',     // 数据库类型
    'DB_HOST'               =>  'www.shopbill.com:3306', // 服务器地址
    'DB_NAME'               =>  'shopbill',          // 数据库名
    'DB_USER'               =>  'root',      // 用户名
    'DB_PWD'                =>  '',          // 密码
    'DB_PORT'               =>  '3306',        // 端口
    'DB_PREFIX'             =>  'shopbill_',    // 数据库表前缀

    'DEFAULT_FILTER'        =>  'trim,htmlspecialchars', // 默认参数过滤方法 用于I函数...
    'IMAGE_CONFIG'=>array(
    	'maxSize'   =>     3145728,// 设置附件上传大小
	    'exts'      =>     array('jpg', 'gif', 'png', 'jpeg'),// 设置附件上传类型
	    'rootPath'  =>     './Public/Uploads/', // 图片上传的保存路径  操作系统的路径
	    'viewPath'  =>     '/Public/Uploads/',  // 图片显示的路径  web路径（就是基于当前域名的）。
	 ),
    // 'SHOW_PAGE_TRACE' => true,
    'URL_CASE_INSENSITIVE' =>true,
    'URL_MODEL'=>2,
);