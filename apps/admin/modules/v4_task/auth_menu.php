<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'v4_task',
    'module_alias' => '狮吼分发平台任务',
    'module_icon'  => 'ios',
    'default_url'=>'v4_task/task/task-list',
    'child_menu' => array(
        array('name'=>'任务列表','url'=>'v4_task/task/task-list','separator'=>'普通任务'),
        
        array('name'=>'添加任务','url'=>'v4_task/task/task-add'),
        array('name'=>'添加任务线1.4','url'=>'v4_task/task/add-task-line'),
        array('name'=>'添加子任务1.4','url'=>'v4_task/task/sub-task-add'),
        array('name'=>'子任务列表','url'=>'v4_task/task/task-children-list'),
        array('name'=>'子任务列表1.4','url'=>'v4_task/task/sub-task-list'),
        //array('name'=>'任务规则','url'=>'v4_task/task/task-rule'),
        array('name'=>'任务标签排序','url'=>'v4_task/task/task-tag'),
//        array('name'=>'推荐标签排序','url'=>'v4_task/task/task-recommend'),
        array('name'=>'签到设置','url'=>'v4_task/task/task-sign'),
        array('name'=>'每日签到统计','url'=>'v4_task/task/task-sign-statistics'),

    ),
    'extra_node'=>array(
        array('name'=>'全部活动模块','url'=>'v4_task/*')
    )
);