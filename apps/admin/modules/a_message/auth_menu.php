<?php
use Illuminate\Support\Facades\Lang;

return array(
    'module_name'  => 'a_message',
    'module_alias' => Lang::get('description.top_mn_message'),
    'module_icon'  => 'android',
    'default_url'=>'a_message/push/list',
    'child_menu' => array(
        array('name'=>Lang::get('description.lm_xx_tslb'),'url'=>'a_message/push/list'),
        array('name'=>Lang::get('description.lm_xx_tzmb'),'url'=>'a_message/tpl/list'),            
    ),
    'extra_node'=>array(  
        array('name'=>'全部消息模块权限','url'=>'a_message/*')      
    )
);