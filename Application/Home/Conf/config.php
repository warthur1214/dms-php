<?php
return array(
	//'配置项'=>'配置值'
    'TMPL_L_DELIM'          =>  '<{',            // 模板引擎普通标签开始标记
    'TMPL_R_DELIM'          =>  '}>',            // 模板引擎普通标签结束标记
    'URL_MODEL'             =>  1,       // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
    // 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)
    'DEFAULT_MODULE'        =>  'Home',  // 默认模块
    'DEFAULT_CONTROLLER'    =>  'Login', // 默认控制器名称
    /* 数据库设置 */
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  '182.106.128.167', // 服务器地址
    'DB_PORT'               =>  '12768',        // 端口
    'DB_NAME'               =>  'auth',  // 数据库名
    'DB_PREFIX'             =>  'tp_',    //表前缀
    'DB_USER'               =>  'risk',      // 用户名
    'DB_PWD'                =>  'Rtest1qaz-2wsX', // 密码
    //redis配置
    'DATA_CACHE_TYPE'                   => 'Redis',
    'REDIS_HOST'                        => '182.106.128.167',
    'REDIS_PORT'                        => 6379,
    'REDIS_AUTH'                        => '3b6d2acf9fac4b7d542567c0e32911c6',
    //codis配置
    'CODIS_HOST'                        => '182.106.128.167',
    'CODIS_PORT'                        => 19000,
    'CODIS_AUTH'                        => '1qaz2wsx',

    // 'ZK_ADDRESS'=> '10.27.1.24:2181,10.27.1.24:2182,10.27.1.24:2183', 
    // 'ZK_PROXYPATH' => '/codis3/codis-demo/proxy',
    // 'ZK_RETRYTIME' => 3,
    //email配置
    'MAIL_HOST'                         => 'smtp.mxhichina.com',
    'MAIL_USER_NAME'                    => '评驾科技-评帐通',
    'MAIL_USER'                         => 'duizhang_oushang@chinaubi.com',
    'MAIL_PASSWORD'                     => 'PingjiaYRUIgbvasdfl#670982ID',
    'EMAIL_TITLE'                       => '长安创新项目对账需求邮件',

    //excel路径
    'EXCEL_FIR_DIR'                     => '/tmp',

    'PLATFORM_ID'                       => 2,
    'ORGAN_TYPE_ID'                     => 4
);