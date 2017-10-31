<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/5/14
 * Time: 14:07
 */
return array(
    'module_name'  => 'zhibo',
    'module_alias' => '直播管理',
    'default_url'=>'zhibo/guest/list',
    'child_menu' => array(
        array('name'=>'主播/嘉宾管理','url'=>'zhibo/guest/list'),
        array('name'=>'直播游戏管理','url'=>'zhibo/game/list'),
        array('name'=>'直播平台管理','url'=>'zhibo/plat/list'),
    ),
    'extra_node'=>array(
        array('name'=>'全部活动模块权限','url'=>'zhibo/*')
    )
);