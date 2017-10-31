<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'a_activity2',
    'module_alias' => '活动2',
    'module_icon'  => 'android',
    'default_url'=>'a_activity2/activity/index',
    'child_menu' => array(
        array('name'=>'任务列表','url'=>'a_activity2/task/task-list'),
        array('name'=>'连续任务列表','url'=>'a_activity2/task/task-chain-list'),
        array('name'=>'添加任务','url'=>'a_activity2/task/task-add'),
        array('name'=>'添加任务线','url'=>'a_activity2/task/task-chain-add'),
    ),
    'extra_node'=>array(
        array('name'=>'全部活动模块','url'=>'a_activity2/*')
    )
);