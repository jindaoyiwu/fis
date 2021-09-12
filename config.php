<?php
$black_list = [
    'dir' => [
        '.svn',
        '.idea',
        'vendor',
        'beerus',
        'esModSearch',
        'g_tapi',
        'hhz_admin',
        'fis',
        'hhz_home',
        'img_server',
        'laravel_admin_base',
        'maxwell_conf',
        'supervisord_conf_bizqueue',
        'tapi',
        '.git'
    ],
    'file' => [

    ]
];
$first_upload = false; //第一是否上传
$project = '/Users/chengyuanzhao/Desktop/work';
//$url = 'http://yapi.qa2.haohaoce.com/receives.php';
$url = 'http://yapi.gray1.haohaozhu.me/receives.php';
$to =  '/data/wwwroot';
return compact('black_list', 'project', 'url', 'to', 'first_upload');
