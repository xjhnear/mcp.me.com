<?php
return array(
    'module_name'  => 'v4_scheme',
    'module_alias' => 'V4Scheme管理',
    'module_icon'  => 'ios',
    'default_url'=>'v4scheme/record/list',
    'child_menu' => array(
        array('name'=>Lang::get('Scheme记录'),'url'=>'v4scheme/record/list'),

     ),
    'extra_node'=>array(     
        array('name'=>'全部Scheme模块权限','url'=>'v4scheme/*')
    )
);  