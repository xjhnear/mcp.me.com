<?php
return array(
    'module_name'  => 'v4_backpack',
    'module_alias' => 'V4背包管理',
    'module_icon'  => 'ios',
    'default_url'=>'v4backpack/goods/list',
    'child_menu' => array(
        array('name'=>Lang::get('物品管理'),'url'=>'v4backpack/goods/list'),
        array('name'=>Lang::get('发放管理'),'url'=>'v4backpack/plan/list'),
        array('name'=>Lang::get('发放记录'),'url'=>'v4backpack/record/list'),
//         array('name'=>Lang::get('背包订单模版管理'),'url'=>'v4backpack/form/list'),
     ),
    'extra_node'=>array(     
        array('name'=>'全部背包模块权限','url'=>'v4backpack/*')
    )
);  