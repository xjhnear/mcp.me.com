<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'v4gamecmt',
    'module_alias' => 'V4玩家情报',
    'default_url'=>'v4gamecmt/comment/index',
    'child_menu' => array(
        array('name'=>'玩家情报','url'=>'v4gamecmt/comment/index'),
    ),
    'extra_node'=>array(
        array('name'=>'全部用户模块权限','url'=>'v4gamecmt/*'),
    )
);