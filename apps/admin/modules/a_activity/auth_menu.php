<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'a_activity',
    'module_alias' => '活动',
    'module_icon'  => 'android',
    'default_url'=>'a_activity/activity/index',
    'child_menu' => array(
        array('name'=>'活动列表','url'=>'a_activity/activity/index'),
        array('name'=>'添加活动','url'=>'a_activity/activity/edit'),
        array('name'=>'分享设置','url'=>'a_system/share/adv-list/android_share_tpl_activity_info'),  
        array('name'=>'新手签到任务','url'=>'a_activity/atask/checkins?type=novice'),
        array('name'=>'连续签到任务','url'=>'a_activity/atask/checkins?type=running'),
        array('name'=>'累计签到任务','url'=>'a_activity/atask/checkins?type=cumulative'),
        array('name'=>'试玩任务列表','url'=>'a_activity/atask/tlist?action_type=1'),
        array('name'=>'分享任务列表','url'=>'a_activity/atask/tlist?action_type=2'),
        array('name'=>'代充任务列表','url'=>'a_activity/atask/tlist?action_type=3'),
        array('name'=>'用户任务列表','url'=>'a_activity/atask/query-task-user'),
        array('name'=>'签到任务说明','url'=>'a_activity/atask/agreement-edit'),
        array('name'=>'黑名单列表','url'=>'a_activity/atask/blacklist'),
    ),
    'extra_node'=>array(
        array('name'=>'全部活动模块','url'=>'a_activity/*')        
    )
);