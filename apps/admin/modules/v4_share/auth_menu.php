<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'v4_share',
    'module_alias' => 'v4自分享',
    'module_icon'  => 'core',
    'default_url'=>'v4_share/home/v4share',
    'child_menu' => array(
        array('name'=>'V4自分享','url'=>'v4_share/home/v4share'),
    ),
    'extra_node'=>array(
        array('name'=>'v4自分享模块权限','url'=>'v4_share/*'),
    )
);