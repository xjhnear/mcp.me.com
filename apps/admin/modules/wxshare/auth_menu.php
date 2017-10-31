<?php
return array(
    'module_name'  => 'wxshare',
    'module_alias' => '微信分享',
    'default_url'=>'wxshare/activity/index',
    'child_menu' => array(
       array('name'=>'活动管理','url'=>'wxshare/activity/list'),
       array('name'=>'礼包管理','url'=>'wxshare/giftbag/list'),
       //array('name'=>'代充管理','url'=>'wxshare/recharge/list'),
       //array('name'=>'实物管理','url'=>'wxshare/goods/list'),
       array('name'=>'分享统计','url'=>'wxshare/activity/report'),
       
    ),
    'extra_node'=>array(       
        array('name'=>'全部用户模块权限','url'=>'wxshare/*'), 
    )
);