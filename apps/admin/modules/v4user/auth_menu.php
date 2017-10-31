<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'v4user',
    'module_alias' => 'V4用户',
    'default_url'=>'v4user/users/index',
    'child_menu' => array(
       array('name'=>'用户列表','url'=>'v4user/users/index'),
       array('name'=>'奖励管理','url'=>'v4user/users/reward-manage'),
       array('name'=>'钻石操作记录','url'=>'v4user/users/operation-list'),
       array('name'=>'批量添加用户','url'=>'v4user/users/batch-check-users-list'),
       //array('name'=>'','url'=>'user/users/create'),
       //array('name'=>'','url'=>'user/users/batch-send'),
       //array('name'=>'批量发游币[安卓]','url'=>'user/users/batch-send-android'),
       //array('name'=>'手机黑名单','url'=>'user/users/mobile-blacklist'),
       //array('name'=>'签到报表','url'=>'user/users/checkin-report'),
       //array('name'=>'安卓游币报表','url'=>'user/users/android-report'),
       //array('name'=>'管理工具','url'=>'user/users/tools'),
    ),
    'extra_node'=>array(       
        array('name'=>'全部用户模块权限','url'=>'v4user/*'),
        array('name'=>'查看用户信息','url'=>'v4user/users/info'),
        array('name'=>'创建用户','url'=>'v4user/users/creat'),
        array('name'=>'修改用户','url'=>'v4user/users/edit'),
        array('name'=>'修改用户密码','url'=>'v4user/users/pwd'),
        array('name'=>'发放游币','url'=>'v4user/users/op-money'),
        array('name'=>'禁言','url'=>'v4user/users/ban'),
        array('name'=>'解除禁言','url'=>'v4user/users/unban'),
        array('name'=>'删除用户发帖及评论','url'=>'v4user/users/clear-post'),
        array('name'=>'屏蔽昵称','url'=>'v4user/users/shield-nickname'),
        array('name'=>'屏蔽头像','url'=>'v4user/users/shield-avatar'),
        array('name'=>'查看IOS游币','url'=>'v4user/users/ios-money'),
        array('name'=>'查看IOS游币记录','url'=>'v4user/users/credit-history'),
        array('name'=>'查看安卓游币记录','url'=>'v4user/users/money-history'),
        array('name'=>'查看Android经验','url'=>'v4user/users/android-money'),
        array('name'=>'添加手机黑名单','url'=>'v4user/users/add-mobile-to-blacklist'),
        array('name'=>'删除手机黑名单','url'=>'v4user/users/del-mobile-from-blacklist'),
    )
);