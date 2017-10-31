<?php
return array(
    'module_name'  => 'v4_box',
    'module_alias' => 'V4百宝箱',
    'module_icon'  => 'ios',
    'default_url'=>'v4box/plan/list',
    'child_menu' => array(
        array('name'=>Lang::get('方案管理'),'url'=>'v4box/plan/list'),
        array('name'=>Lang::get('订单管理'),'url'=>'v4box/order/list'),
     ),
    'extra_node'=>array(     
        array('name'=>'全部百宝箱权限','url'=>'v4box/*')
    )
);  