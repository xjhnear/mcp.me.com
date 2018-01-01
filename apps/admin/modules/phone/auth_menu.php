<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'phone',
    'module_alias' => '手机号码管理',
    'module_icon'  => 'all',
    'default_url'=>'phone/batch/list',
    'child_menu' => array(
        array('name'=>'批次管理','url'=>'phone/batch/list'),
        array('name'=>'类别管理','url'=>'phone/category/list'),
    ),
    'extra_node'=>array(
        array('name'=>'全部手机号码模块权限','url'=>'phone/*'),
        array('name'=>'批次管理','url'=>'phone/batch/*'),
        array('name'=>'类别管理','url'=>'phone/category/*')
    )
);