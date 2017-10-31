<?php
return array(
    'module_name'  => 'other',
    'module_alias' => '其他管理',
    'module_icon'  => 'core',
    'default_url'=>'other/discovery/list',
    'child_menu' => array(
        array('name'=>'发现列表','url'=>'other/discovery/list','separator'=>'发现'),
        array('name'=>'添加新发现','url'=>'other/discovery/save'),
        array('name'=>'游戏栏目管理','url'=>'other/discovery/game-column-list','separator'=>'游戏'),
    ),
    'extra_node'=>array(   
        array('name'=>'全部其他管理权限','url'=>'other/*')
    )
);