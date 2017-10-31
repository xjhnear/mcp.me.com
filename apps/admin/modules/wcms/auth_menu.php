<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'wcms',
    'module_alias' => 'CMS',
    'module_icon'  => 'ios',
    'default_url'=>'wcms/article/index',
    'child_menu' => array(
        array('name'=>'文章管理','url'=>'wcms/article/index'),
        array('name'=>'图集管理','url'=>'wcms/picture/index'),
        array('name'=>'视频管理','url'=>'wcms/video/index'),
        array('name'=>'分类管理','url'=>'wcms/category/index'),
        array('name'=>'闪屏设置','url'=>'wcms/home/shan'),
        array('name'=>'顶部弹窗设置','url'=>'wcms/home/top-adv','separator'=>'首页设置'),         
        array('name'=>'首屏背投轮播图','url'=>'wcms/home/slide-list/one'),
        array('name'=>'首屏推荐游戏','url'=>'wcms/home/game-list'),
        array('name'=>'首屏轮播图','url'=>'wcms/home/slide-list/two'),
        array('name'=>'首屏六个图推','url'=>'wcms/home/slide-list/third'),
        array('name'=>'首屏多头条','url'=>'wcms/home/topline-list'),
        array('name'=>'二屏背景图','url'=>'wcms/home/bgimage/two'),
        array('name'=>'二屏轮播图','url'=>'wcms/home/slide-list/fourth'),
        array('name'=>'二屏游戏文章Tag','url'=>'wcms/home/gametag-list'),
        array('name'=>'二屏推荐商品','url'=>'wcms/home/goods-list'),
        array('name'=>'二屏热门活动','url'=>'wcms/home/activity-list/fifth'),
        array('name'=>'二屏热门礼包','url'=>'wcms/home/activity-list/sixth'), 
        array('name'=>'三屏直播视频','url'=>'wcms/home/live-code'),
        array('name'=>'新游期待背景图','url'=>'wcms/home/bgimage/fourth'),
        array('name'=>'三屏新游期待榜','url'=>'wcms/home/await-list'),
        array('name'=>'三屏新游预告','url'=>'wcms/home/advance'),
        array('name'=>'三屏专题','url'=>'wcms/home/special-list'),
        array('name'=>'新游评测推荐','url'=>'wcms/home/slide-list/fifth'),
        array('name'=>'三屏视频推荐','url'=>'wcms/home/slide-list/seventh'),
        array('name'=>'三屏原创栏目','url'=>'wcms/home/slide-list/eighth'),
        array('name'=>'三屏产业推荐','url'=>'wcms/home/slide-list/ninth'),
        array('name'=>'三屏产业专栏','url'=>'wcms/home/slide-list/tenth'),  
        array('name'=>'四屏背景游戏设置','url'=>'wcms/home/fourth-projection'),
        array('name'=>'四屏推荐攻略','url'=>'wcms/home/guide-list'), 
        array('name'=>'排行榜设置','url'=>'wcms/home/rank-setting'),   
    ),    
    'extra_node'=>array(
        array('name'=>'全部CMS模块权限','url'=>'wcms/*'),   
    )
);