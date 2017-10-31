<?php
return array(
    'module_name'  => 'zt_activity',
    'module_alias' => '专题活动',
    'default_url'=>'zt_activity/blcx/message-list',
    'child_menu' => array(
        array('name'=>'百炼成仙留言审核','url'=>'zt_activity/blcx/message-list'),
        array('name'=>'百炼成仙月儿海选','url'=>'zt_activity/blcx/audit-list'),
        array('name'=>'Web首页期待榜','url'=>'zt_activity/hope/edit'),
        array('name'=>'ChinaJoy公告','url'=>'zt_activity/chinajoy/edit'),
        array('name'=>'ChinaJoy跑会','url'=>'zt_activity/chinajoy/guide'),
        array('name'=>'ChinaJoy厂商','url'=>'zt_activity/chinajoy/manufacturers'),
        array('name'=>'ChinaJoy直播行程','url'=>'zt_activity/chinajoy/direct-edit'),
        array('name'=>'ChinaJoy弹幕管理','url'=>'zt_activity/chinajoy/barrage'),
        array('name'=>'礼包活动管理','url'=>'zt_activity/yxdlibao/search'),
        array('name'=>'活动管理','url'=>'zt_activity/hd/search'),
    ),
    'extra_node'=>array(
        array('name'=>'全部活动模块权限','url'=>'zt_activity/*')
    )
);