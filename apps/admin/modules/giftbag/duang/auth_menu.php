<?php
return array(
    'module_name'  => 'duang',
    'module_alias' => 'Duang活动',
    'default_url'=>'duang/activity/index',
    'child_menu' => array(
        array('name'=>'活动管理','url'=>'duang/activity/list'),
        array('name'=>'图片管理','url'=>'duang/pic/list'),
    ),
    'extra_node'=>array(       
        array('name'=>'全部用户模块权限','url'=>'duang/*'),
    )
);