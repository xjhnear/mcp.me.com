<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'v4_tuiguang',
    'module_alias' => 'v4推广',
    'module_icon'  => 'ios',
    'default_url'=>'v4_tuiguang/home/commission',
    'child_menu' => array(
        array('name'=>'提现管理','url'=>'v4_tuiguang/home/commission'),
        array('name'=>'推广用户','url'=>'v4_tuiguang/home/user'),
        array('name'=>'推广员','url'=>'v4_tuiguang/home/promoter'),
        array('name'=>'帐户收入','url'=>'v4_tuiguang/home/yh-income'),
        array('name'=>'用户充值','url'=>'v4_tuiguang/home/money-record'),
       // array('name'=>'游币流水1','url'=>'v4_tuiguang/home/yb-record'),
        array('name'=>'游戏分成设置','url'=>'v4_tuiguang/home/commission-setup'),
        array('name'=>'统一分成设置','url'=>'v4_tuiguang/home/setup-all'),
        array('name'=>'攻略分成设置','url'=>'v4_tuiguang/home/setup-gl'),
       
    ),
    'extra_node'=>array(
        array('name'=>'全部推广模块权限','url'=>'v4_tuiguang/*'),
    )
);