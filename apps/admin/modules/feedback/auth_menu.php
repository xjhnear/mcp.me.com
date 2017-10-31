<?php
return array(
    'module_name'  => 'feedback',
    'module_alias' => Lang::get('description.top_mn_feedback'),
    'default_url'=>'feedback/chat/users',
    'child_menu' => array(
        array('name'=>Lang::get('description.lm_yhfk_fklb'),'url'=>'feedback/chat/users'),
        array('name'=>'Android反馈','url'=>'feedback/achat/list'),
    ),
    'extra_node'=>array(   
        array('name'=>'全部反馈模块权限','url'=>'feedback/*')     
    )
);