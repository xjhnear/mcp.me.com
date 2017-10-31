<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'user',
    'module_alias' => Lang::get('description.top_mn_user'),
    'default_url'=>'user/users/index',
    'child_menu' => array(
       array('name'=>Lang::get('description.lm_yh_yhgl'),'url'=>'user/users/index'),
       array('name'=>Lang::get('description.lm_yh_cjyh'),'url'=>'user/users/create'),
       array('name'=>Lang::get('description.lm_yh_plfyb'),'url'=>'user/users/batch-send'),
       array('name'=>'批量发游币[安卓]','url'=>'user/users/batch-send-android'),
       array('name'=>'手机黑名单','url'=>'user/users/mobile-blacklist'),
       array('name'=>'签到报表','url'=>'user/users/checkin-report'),
       array('name'=>'安卓游币报表','url'=>'user/users/android-report'),
       array('name'=>'管理工具','url'=>'user/users/tools'),             
    ),
    'extra_node'=>array(       
        array('name'=>'全部用户模块权限','url'=>'user/*'),        
        array('name'=>'查看用户信息','url'=>'user/users/info'),
        array('name'=>'创建用户','url'=>'user/users/creat'),
        array('name'=>'修改用户','url'=>'user/users/edit'),
        array('name'=>'修改用户密码','url'=>'user/users/pwd'),
        array('name'=>'发放游币','url'=>'user/users/op-money'),
        array('name'=>'禁言','url'=>'user/users/ban'),
        array('name'=>'解除禁言','url'=>'user/users/unban'),
        array('name'=>'删除用户发帖及评论','url'=>'user/users/clear-post'),
        array('name'=>'屏蔽昵称','url'=>'user/users/shield-nickname'),
        array('name'=>'屏蔽头像','url'=>'user/users/shield-avatar'),
        array('name'=>'查看IOS游币','url'=>'user/users/ios-money'),
        array('name'=>'查看IOS游币记录','url'=>'user/users/credit-history'),
        array('name'=>'查看安卓游币记录','url'=>'user/users/money-history'),
        array('name'=>'查看Android经验','url'=>'user/users/android-money'),        
        array('name'=>'添加手机黑名单','url'=>'user/users/add-mobile-to-blacklist'),
        array('name'=>'删除手机黑名单','url'=>'user/users/del-mobile-from-blacklist'), 
    )
);