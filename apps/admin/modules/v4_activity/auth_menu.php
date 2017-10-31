<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'v4_activity',
    'module_alias' => 'v4活动管理',
    'module_icon'  => 'ios',
    'default_url'=>'v4activity/activity/list',
    'child_menu' => array(
        array('name'=>'活动列表','url'=>'v4activity/activity/list'),
        array('name'=>'游戏列表','url'=>'v4activity/activity/h5list','separator'=>'H5活动'),
        array('name'=>'分享设置','url'=>'v4activity/activity/h5share'),
        //array('name'=>'','url'=>'v4activity/activity/list'),
        //array('name'=>'礼包活动列表','url'=>'v4giftbag/giftactivity/search'),
    ),
    'extra_node'=>array(
        array('name'=>'全部活动模块','url'=>'v4activity/*'),
    )
);