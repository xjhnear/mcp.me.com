<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'IOS_activity',
    'module_alias' => '任务',
    'module_icon'  => 'ios',
    'default_url'=>'IOS_activity/task/task-list',
    'child_menu' => array(
        array('name'=>'任务列表','url'=>'IOS_activity/task/task-list'),
        array('name'=>'添加任务','url'=>'IOS_activity/task/task-add'),
        array('name'=>'子任务列表','url'=>'IOS_activity/task/task-children-list'),
    ),
    'extra_node'=>array(
        array('name'=>'全部活动模块','url'=>'IOS_activity/*')
    )
);