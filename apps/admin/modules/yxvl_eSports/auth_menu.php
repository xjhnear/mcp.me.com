<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'yxvl_eSports',
    'module_alias' => 'VLongTV后台',
    'module_icon'  => 'core',
    'default_url'=>'yxvl_eSports/VltvLive/index',
    'child_menu' => array(
        array('name'=>'直播代码管理','url'=>'yxvl_eSports/VltvLive/index','separator'=>'vlong直播'),
        array('name'=>'用户管理','url'=>'yxvl_eSports/VltvLive/user'),
        array('name'=>'文章管理','url'=>'yxvl_eSports/article/index','separator'=>'游戏电竞'),
        array('name'=>'电竞专栏','url'=>'yxvl_eSports/column/index'),
        array('name'=>'直播管理','url'=>'yxvl_eSports/live/index'),
        array('name'=>'视频管理','url'=>'yxvl_eSports/video/index'),
        array('name'=>'赛事管理','url'=>'yxvl_eSports/sports/index'),
        array('name'=>'分类管理','url'=>'yxvl_eSports/category/index'),
        array('name'=>'中间游戏直播','url'=>'yxvl_eSports/home/game-video'),
        array('name'=>'头部游戏专区','url'=>'yxvl_eSports/home/game-zq'),
        array('name'=>'头部热门电竞','url'=>'yxvl_eSports/home/hot-dj'),
        array('name'=>'头部左侧幻灯','url'=>'yxvl_eSports/home/left-hd'),
        array('name'=>'头部右侧赛事','url'=>'yxvl_eSports/home/right-ss'),
        array('name'=>'赛事中心战队','url'=>'yxvl_eSports/home/ss-zd'),
        array('name'=>'底部广告位','url'=>'yxvl_eSports/home/gg-footer'),
        array('name'=>'头部广告位','url'=>'yxvl_eSports/home/gg-header'),
        array('name'=>'网页描述','url'=>'yxvl_eSports/home/page-desc'),

    ),
    'extra_node'=>array(
        array('name'=>'全部VLongTV后台权限','url'=>'yxvl_eSports/*'),
    )
);