<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'a_activity2',
    'module_alias' => '安卓任务',
    'module_icon'  => 'android',
    'default_url'=>'a_activity2/task/task-list',
    'child_menu' => array(
        array('name'=>'任务列表','url'=>'a_activity2/task/task-list'),
        array('name'=>'任务线列表','url'=>'a_activity2/task/task-chain-list'),
        array('name'=>'子任务列表','url'=>'a_activity2/task/task-children-list'),
    ),
    'extra_node'=>array(
        array('name'=>'全部活动模块','url'=>'a_activity2/*')
    )
);