<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'admin',
    'module_alias' => '高级管理',
    'module_icon'  => 'core',
    'default_url'=>'admin/group/list',
    'child_menu' => array(
        array('name'=>Lang::get('description.lm_xt_qxz'),'url'=>'admin/group/list'),
        array('name'=>Lang::get('description.lm_xt_glylb'),'url'=>'admin/admin/list'),
        array('name'=>Lang::get('description.lm_xt_mkgl'),'url'=>'admin/module/list'),
        //array ('name' =>Lang::get('description.lm_xt_czjl'), 'url' =>'admin/operate/index' )        
    ),
    'extra_node'=>array(
        array('name'=>'全部系统模块权限','url'=>'admin/*'),        
        array('name'=>'权限管理','url'=>'admin/group/*'),
        array('name'=>'账号管理','url'=>'admin/admin/*'),
        array('name'=>'模块管理','url'=>'admin/module/*'),
        array('name'=>'卸载模块','url'=>'admin/module/uninstall'),
        array('name'=>'修改后台密码','url'=>'admin/admin/modify-pwd')
    )
);