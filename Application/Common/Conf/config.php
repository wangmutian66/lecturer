<?php
	return array(
		'DB_TYPE'               =>  'mysql',     // 数据库类型
	    'DB_HOST'               =>  'localhost', // 服务器地址192.168.0.250 localhost
	    'DB_NAME'               =>  'lecturer',          // 数据库名
	    'DB_USER'               =>  'root',      // 用户名
	    'DB_PWD'                =>  '',          // 密码
	    'DB_PORT'               =>  '3306',        // 端口
	    'DB_PREFIX'             =>  'le_',    // 数据库表前缀
		'DB_CHARSET'            =>  'utf8',      // 数据库编码默认采用utf8
	    'DB_FIELD_CACHE'        =>  false,
	    'HTML_CACHE_ON'         =>  false,
	    'LOG_RECORD' => false,    // 关闭日志记录
	    'DB_PARAMS'               => array("persist"=>True),
		//短信配置
		'SMS_USER'				=> "57665",		//短信用户名
		'SMS_PWD'				=> "h5ef92",	//短信密码   
	    // 'SESSION_OPTIONS'         =>  array(
	    //     'name'                =>  '',                    //设置session名
	    //     'expire'              =>  3600*30,               //SESSION保存15天
	    //     'use_trans_sid'       =>  1,                     //跨页传递
	    //     'use_only_cookies'    =>  1,                     //是否只开启基于cookies的session的会话方式
	    // ),
	    
	    //'URL_MODEL' => 2,
	    //'URL_HTML_SUFFIX'=>''
	    'SALES_POWER_SESSION'=>4
	);
?>