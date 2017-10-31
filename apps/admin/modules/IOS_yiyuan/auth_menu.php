<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'IOS_yiyuan',
    'module_alias' => '一元购',
    'module_icon'  => 'ios',
    'default_url'=>'IOS_yiyuan/goods/list',
    'child_menu' => array(
//        array('name'=>'游戏列表','url'=>'liansai/home/game-list'),
        array('name'=>'商品','url'=>'IOS_yiyuan/goods/list'),
        array('name'=>'系列商品','url'=>'IOS_yiyuan/xilie/list'),
        array('name'=>'订单','url'=>'IOS_yiyuan/order/list'),
        array('name'=>'广告位','url'=>'IOS_yiyuan/adv/list'),
        array('name'=>'收货模版','url'=>'IOS_yiyuan/template/list'),
        array('name'=>'活动期号','url'=>'IOS_yiyuan/lottery/list'),
        array('name'=>'收货地址','url'=>'IOS_yiyuan/address/list'),
        array('name'=>'用户协议信息','url'=>'IOS_yiyuan/usermessage/add'),
        array('name'=>'参与人数','url'=>'IOS_yiyuan/statistics/number-list'),
        array('name'=>'参与份数','url'=>'IOS_yiyuan/statistics/copies-list'),
        array('name'=>'用户消耗游币','url'=>'IOS_yiyuan/statistics/coin-list'),

    ),
    'extra_node'=>array(
        array('name'=>'全部一元购管理权限','url'=>'IOS_yiyuan/*'),
    )
);