<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'liansai',
    'module_alias' => '联赛管理',
    'module_icon'  => 'IOS',
    'default_url'=>'IOS_liansai/home/list',
    'child_menu' => array(
//        array('name'=>'游戏列表','url'=>'liansai/home/game-list'),
        array('name'=>'赛事列表','url'=>'liansai/home/list'),
        array('name'=>'战队列表','url'=>'liansai/home/team-list'),
        array('name'=>'比赛记录','url'=>'liansai/home/match-list'),

    ),
    'extra_node'=>array(
        array('name'=>'全部联赛管理权限','url'=>'IOS_liansai/*'),
    )
);