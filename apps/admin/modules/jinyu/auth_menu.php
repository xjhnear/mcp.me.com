<?php
return array(
    'module_name'  => 'jinyu',
    'module_alias' => '金娱',
    'module_icon'  => 'core',
    'default_url'=>'jinyu/price/list',
    'child_menu' => array(
        array('name'=>'奖项','url'=>'jinyu/price/list'),
        array('name'=>'游戏','url'=>'jinyu/game/list'),
    ),
    'extra_node'=>array(   
        array('name'=>'全部金娱管理权限','url'=>'jinyu/*')
    )
);