<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'recharge',
    'module_alias' => '游币充值',
    'module_icon'  => 'android',
    'default_url'=>'recharge/order/list',
    'child_menu' => array(
//        array('name'=>'游戏列表','url'=>'liansai/home/game-list'),
        array('name'=>'充值订单','url'=>'recharge/order/list'),
        array('name'=>'充值统计','url'=>'recharge/statistics/list'),

    ),
    'extra_node'=>array(
        array('name'=>'全部权限游币充值','url'=>'recharge/*'),
    )
);