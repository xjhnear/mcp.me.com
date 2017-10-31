<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'common',
    'module_alias' => '首页',
    'module_icon'  => '',
    'default_url'=>'common/home/index',
    'child_menu' => array(   
        array('name'=>'个人首页','url'=>'common/home/index')                  
    ),
    'extra_node'=>array(  
        array('name'=>'个人首页','url'=>'common/home/index'),
        array('name'=>'修改个人资料','url'=>'common/home/edit-profile'),
        array('name'=>'上传图片','url'=>'common/uploader/*'),
              
    )
);