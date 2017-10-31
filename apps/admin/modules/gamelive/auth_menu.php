<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'gamelive',
    'module_alias' => '游戏直播',
    'module_icon'  => 'core',
    'default_url'=>'gamelive/home/index',
    'child_menu' => array(
        array('name'=>'首页轮播图','url'=>'gamelive/home/slide-list'),
        array('name'=>'首页排期表','url'=>'gamelive/home/week-list'),
        array('name'=>'文章管理','url'=>'gamelive/article/list'),
        array('name'=>'视频管理','url'=>'gamelive/video/list'),
        array('name'=>'游戏管理','url'=>'gamelive/game/list'),
        array('name'=>'主播管理','url'=>'gamelive/anchor/list'),
        array('name'=>'标签管理','url'=>'gamelive/category/list'),
        array('name'=>'栏目管理','url'=>'gamelive/column/list'),

    ),
    'extra_node'=>array(
        array('name'=>'全部游戏模块','url'=>'gamelive/*'),
    )
);