<?php
return array(
    'module_name'  => 'v4message',
    'module_alias' => 'V4消息',
    'module_icon'  => 'ios',
    'default_url'=>'v4message/push/list',
    'child_menu' => array(
        array('name'=>'消息列表','url'=>'v4message/push/list'),
        array('name'=>'消息模板','url'=>'v4message/tpl/list'),
        array('name'=>'自动推送','url'=>'v4message/tpl2/list'),
        array('name'=>'消息屏蔽','url'=>'v4message/filter/list'),
        //array('name'=>'批量发消息','url'=>'v4message/push/batch-push'),
    ),
    'extra_node'=>array(
        array('name'=>'全部消息模块权限','url'=>'v4message/*')
    )
);