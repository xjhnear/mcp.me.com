<?php
return array(
    'module_name'  => 'v4_shareaccount',
    'module_alias' => '共享账号管理',
    'module_icon'  => 'ios',
    'default_url'=>'v4shareaccount/account/list',
    'child_menu' => array(
        array('name'=>Lang::get('共享账号'),'url'=>'v4shareaccount/account/list'),
        array('name'=>Lang::get('使用说明'),'url'=>'v4shareaccount/account/info'),

     ),
    'extra_node'=>array(     
        array('name'=>'全部共享账号模块权限','url'=>'v4shareaccount/*')
    )
);  