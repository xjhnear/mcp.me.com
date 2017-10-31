<?php
return array(
    'module_name'  => 'v4_monitor',
    'module_alias' => 'V4监控管理',
    'module_icon'  => 'ios',
    'default_url'=>'v4monitor/record/list',
    'child_menu' => array(
        array('name'=>Lang::get('主线程管理'),'url'=>'v4monitor/record/list'),
        array('name'=>Lang::get('子接口管理'),'url'=>'v4monitor/record/process-list'),
        array('name'=>Lang::get('删除记录'),'url'=>'v4monitor/record/delete'),
     ),
    'extra_node'=>array(     
        array('name'=>'全部监控模块权限','url'=>'v4monitor/*')
    )
);  