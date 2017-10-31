<?php
return array(
    'module_name'  => 'v4system',
    'module_alias' => 'v4设置',
    'module_icon'  => 'ios',
    'default_url'=>'v4system/config/setting',
    'child_menu' => array(
        array('name'=>'APP设置','url'=>'v4system/config/setting'),
        array('name'=>'分享设置','url'=>'v4system/config/share-setting'),
        array('name'=>'新手奖励','url'=>'v4system/config/money-setting'),
        array('name'=>'签到奖励','url'=>'v4system/config/sign-money'),
        array('name'=>'举报管理','url'=>'v4system/report/list'),
        array('name'=>'用户区服管理','url'=>'v4system/game/user-as-list'),
        array('name'=>'游戏区服管理','url'=>'v4system/game/as-list'),
        array('name'=>'游戏渠道管理','url'=>'v4system/game/channel-list'),
        array('name'=>'游戏充值管理','url'=>'v4system/game/recharge-list'),
        array('name'=>'批量管理','url'=>'v4system/kvsetting/list'),

        array('name'=>'APP设置','url'=>'v4system/config/gl-setting','separator'=>'攻略设置'),
        array('name'=>'分享设置','url'=>'v4system/config/gl-share-setting'),
    ),
    'extra_node'=>array(
        array('name'=>'全部设置模块权限','url'=>'v4system/*')
    )
);