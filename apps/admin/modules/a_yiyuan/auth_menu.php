<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'a_yiyuan',
    'module_alias' => '一元购',
    'module_icon'  => 'android',
    'default_url'=>'a_yiyuan/goods/list',
    'child_menu' => array(
//        array('name'=>'游戏列表','url'=>'liansai/home/game-list'),
        array('name'=>'商品','url'=>'a_yiyuan/goods/list'),
        array('name'=>'系列商品','url'=>'a_yiyuan/xilie/list'),
        array('name'=>'订单','url'=>'a_yiyuan/order/list'),
        array('name'=>'广告位','url'=>'a_yiyuan/adv/list'),
        array('name'=>'收货模版','url'=>'a_yiyuan/template/list'),
        array('name'=>'活动期号','url'=>'a_yiyuan/lottery/list'),
        array('name'=>'收货地址','url'=>'a_yiyuan/address/list'),
        array('name'=>'参与人数','url'=>'a_yiyuan/statistics/number-list'),
        array('name'=>'参与份数','url'=>'a_yiyuan/statistics/copies-list'),
        array('name'=>'用户消耗游币','url'=>'a_yiyuan/statistics/coin-list'),
        array('name'=>'用户协议信息','url'=>'a_yiyuan/usermessage/add'),
    ),
    'extra_node'=>array(
        array('name'=>'全部一元购管理权限','url'=>'a_yiyuan/*'),
    )
);