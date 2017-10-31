<?php
use Illuminate\Support\Facades\Lang;

return array(
    'module_name'  => 'message',
    'module_alias' => Lang::get('description.top_mn_message'),
    'module_icon'  => 'ios',
    'default_url'=>'message/push/list',
    'child_menu' => array(
        array('name'=>Lang::get('description.lm_xx_tslb'),'url'=>'message/push/list'),
        array('name'=>Lang::get('description.lm_xx_tzmb'),'url'=>'message/tpl/list'),
        array('name'=>'批量发消息','url'=>'message/push/batch-push'),            
    ),
    'extra_node'=>array(  
        array('name'=>'全部消息模块权限','url'=>'message/*')      
    )
);