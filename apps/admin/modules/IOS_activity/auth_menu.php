<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'IOS_activity',
    'module_alias' => '任务',
    'module_icon'  => 'ios',
    'default_url'=>'IOS_activity/task/task-list',
    'child_menu' => array(
        array('name'=>'任务列表','url'=>'IOS_activity/task/task-list','separator'=>'普通任务'),
        
        array('name'=>'添加任务','url'=>'IOS_activity/task/task-add'),
        array('name'=>'子任务列表','url'=>'IOS_activity/task/task-children-list'),
        array('name'=>'任务规则','url'=>'IOS_activity/task/task-rule'),
        
        array('name'=>'新手任务列表','url'=>'IOS_activity/task/task-new-list','separator'=>'新手任务'),
        array('name'=>'添加任务','url'=>'IOS_activity/task/task-new-add'),

    ),
    'extra_node'=>array(
        array('name'=>'全部活动模块','url'=>'IOS_activity/*')
    )
);