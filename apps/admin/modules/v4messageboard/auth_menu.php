<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'v4messageboard',
    'module_alias' => 'V4留言板',
    'default_url'=>'v4messageboard/comment/index',
    'child_menu' => array(
        array('name'=>'留言板','url'=>'v4messageboard/comment/index'),
    ),
    'extra_node'=>array(
        array('name'=>'全部留言板模块权限','url'=>'v4messageboard/*'),
    )
);