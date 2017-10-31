<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'zhuanqu',
    'module_alias' => '专区专题',
    'module_icon'  => 'core',
    'default_url'=>'zhuanqu/home/list',
    'child_menu' => array(
        array('name'=>'专题列表','url'=>'zhuanqu/home/list'),
    ),
    'extra_node'=>array(
        array('name'=>'全部管理权限','url'=>'zhuanqu/*'),
    )
);