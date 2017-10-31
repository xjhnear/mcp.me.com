<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'IOS_recharge',
    'module_alias' => '充值管理',
    'module_icon'  => 'ios',
    'default_url'=>'IOS_recharge/order/list',
    'child_menu' => array(
//        array('name'=>'游戏列表','url'=>'liansai/home/game-list'),
        array('name'=>'充值订单','url'=>'IOS_recharge/order/list'),
        array('name'=>'充值统计','url'=>'IOS_recharge/statistics/list'),

    ),
    'extra_node'=>array(
        array('name'=>'全部充值权限','url'=>'IOS_recharge/*'),
    )
);