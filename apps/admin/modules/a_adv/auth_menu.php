<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'a_adv',
    'module_alias' => '推荐位管理',
    'module_icon'  => 'android',
    'default_url'=>'',
    'child_menu' => array(
        array('name'=>'广告位管理','url'=>'a_adv/recommend/image-place-list'),
        array('name'=>'位置管理','url'=>'a_adv/recommend/location-list'),
        
        //array('name'=>'图片推荐位','url'=>'a_adv/recommend/image-place-list'),        
           
    ),
    'extra_node'=>array(
        array('name'=>'全部活动模块','url'=>'a_adv/*')        
    )
);