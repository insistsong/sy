<?php
//配置文件
return [
	// // 视图输出字符串内容替换
    'view_replace_str' => [

            '__STATIC_URL__' => 'http://sy.mengtiancai.com/static',
            '__ROOT_URL__' => 'http://sy.mengtiancai.com/admin',

    ],
    'template'  =>  [
    'layout_on'     =>  true,
    'layout_name'   =>  'public/layout',
    'layout_item'   =>  '{__CONTENT__}'
	],
    'auto_timestamp' => true,


];