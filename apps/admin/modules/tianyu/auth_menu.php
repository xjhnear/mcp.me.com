<?php
return array(
    'module_name'  => 'tianyu',
    'module_alias' => '天娱',
    'module_icon'  => 'core',
    'default_url'=>'tianyu/price/list',
    'child_menu' => array(
        array('name'=>'奖项','url'=>'tianyu/price/list'),
        array('name'=>'游戏','url'=>'tianyu/game/list'),
    ),
    'extra_node'=>array(   
        array('name'=>'全部天娱管理权限','url'=>'tianyu/*')
    )
);