<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'tuiguang',
    'module_alias' => '推广',
    'module_icon'  => 'core',
    'default_url'=>'tuiguang/home/commission',
    'child_menu' => array(
        array('name'=>'提现管理','url'=>'tuiguang/home/commission'),
        array('name'=>'推广用户','url'=>'tuiguang/home/user'),
        array('name'=>'推广员','url'=>'tuiguang/home/promoter'),
        array('name'=>'现金流水','url'=>'tuiguang/home/money-record'),
        array('name'=>'游币流水','url'=>'tuiguang/home/yb-record'),
        array('name'=>'统一分成设置','url'=>'tuiguang/home/commission-setup'),
      //array('name'=>'365交易分成','url'=>'tuiguang/home/transaction-list'),
        array('name'=>'Android SDK分成','url'=>'tuiguang/home/transaction-android'),
    ),
    'extra_node'=>array(
        array('name'=>'全部推广模块权限','url'=>'tuiguang/*'),
    )
);