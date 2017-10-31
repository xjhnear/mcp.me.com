<?php
return array(
    'module_name'  => 'v4_adv',
    'module_alias' => 'v4广告管理',
    'module_icon'  => 'ios',
    'default_url'=>'v4adv/popup/list',
    'child_menu' => array(
        array('name'=>'首页弹窗广告','url'=>'v4adv/popup/list'),
        array('name'=>'首页轮播广告','url'=>'v4adv/carousel/list'),
        array('name'=>'视频轮播广告','url'=>'v4adv/video/list'),
        array('name'=>'首页推荐位','url'=>'v4adv/recommend/list'),
        array('name'=>'首页主Banner','url'=>'v4adv/banner/list'),
        array('name'=>'首页副Banner','url'=>'v4adv/vicebanner/list'),
        array('name'=>'首页Banner','url'=>'v4adv/indexbanner/list'),
        array('name'=>'网游推荐位','url'=>'v4adv/webgame/list'),
        array('name'=>'单机推荐位','url'=>'v4adv/pcgame/list'),
        array('name'=>'游戏详情','url'=>'v4adv/gameinfo/list'),
        array('name'=>'游戏详情顶部','url'=>'v4adv/gameinfotop/list'),
        array('name'=>'猜你喜欢','url'=>'v4adv/guessyoulike/list'),
        array('name'=>'启动页','url'=>'v4adv/startup/list'),
        array('name'=>'任务中心','url'=>'v4adv/task/list'),
        array('name'=>'下载按钮','url'=>'v4adv/btndownload/list'),
        array('name'=>'清除广告缓存','url'=>'v4adv/core/delcache'),
    ),
    'extra_node'=>array(
        array('name'=>'全部广告模块','url'=>'v4adv/*'),
    )
);