<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'neirong',
    'module_alias' => '内容流',
    'module_icon'  => 'core',
    'default_url'=>'neirong/home/index',
    'child_menu' => array(
        array('name'=>'列表','url'=>'neirong/home/index'),
    ),
    'extra_node'=>array(
        array('name'=>'内容流模块权限','url'=>'neirong/*'),
    )
);