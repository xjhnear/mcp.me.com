<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'v4_my',
    'module_alias' => 'v4我的管理',
    'module_icon'  => 'ios',
    'default_url'=>'v4my/medal/list',
    'child_menu' => array(
        array('name'=>'勋章管理','url'=>'v4my/medal/list'),
        array('name'=>'游戏相册','url'=>'v4my/image/list'),
        //array('name'=>'礼包活动列表','url'=>'v4giftbag/giftactivity/search'),
    ),
    'extra_node'=>array(
        array('name'=>'全部勋章模块','url'=>'v4my/*'),
    )
);