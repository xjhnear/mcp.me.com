<?php
return array(
    'module_name'  => 'union',
    'module_alias' => '综合管理',
    'default_url'=>'union/cash/list',
    'child_menu' => array(
        array('name'=>'返现管理','url'=>'union/cash/list'),
        array('name'=>'综合轮播','url'=>'union/banner/list')
    ),
    'extra_node'=>array(       
        array('name'=>'全部综合模块权限','url'=>'union/*'),
    )
);