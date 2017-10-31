<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'liansais',
    'module_alias' => 'PC导量活动',
    'module_icon'  => 'core',
    'default_url'=>'liansais/home/list',
    'child_menu' => array(
        array('name'=>'列表','url'=>'liansais/home/list'),

    ),
    'extra_node'=>array(
        array('name'=>'全部联赛管理权限','url'=>'liansais/*'),
    )
);