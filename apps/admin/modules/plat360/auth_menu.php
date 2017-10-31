<?php
return array(
    'module_name'  => 'plat360',
    'module_alias' => '360平台游戏下载同步管理',
    'default_url'=>'plat360/mapping/list',
    'child_menu' => array(
        array('name'=>'游戏关系映射表','url'=>'plat360/mapping/list'),
        array('name'=>'同步地址记录','url'=>'plat360/sync/list'),
        array('name'=>'映射www','url'=>'plat360/mapping/associate-game'),         //注意：只能上线时执行此方法
        array('name'=>'映射mobile','url'=>'plat360/mapping/associate-game2'),     //注意：只能上线时执行此方法
        array('name'=>'执行同步','url'=>'plat360/mapping/start-sync'),
        array('name'=>'回滚前一天所有同步','url'=>'plat360/mapping/go-back'),

    ),
    'extra_node'=>array(     
        array('name'=>'全部同步下载地址模块权限','url'=>'plat360/*')
    )
);