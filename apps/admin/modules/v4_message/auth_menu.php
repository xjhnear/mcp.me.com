<?php
use Illuminate\Support\Facades\Lang;

return array(
    'module_name'  => 'v4_message',
    'module_alias' => '消息V4',
    'module_icon'  => 'ios',
    'default_url'=>'v4_message/push/list',
    'child_menu' => array(
        array('name'=>'消息推送','url'=>'v4_message/push/list'),   
    ),
    'extra_node'=>array(  
        array('name'=>'全部消息V4模块权限','url'=>'v4_message/*')      
    )
);