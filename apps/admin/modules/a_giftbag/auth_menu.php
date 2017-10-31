<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'a_giftbag',
    'module_alias' => '礼包',
    'module_icon'  => 'android',
    'default_url'=>'a_giftbag/gift/search',
    'child_menu' => array(
        array('name'=>'礼包列表','url'=>'a_giftbag/gift/search'),
        array('name'=>'添加礼包','url'=>'a_giftbag/gift/add'),  
        array('name'=>'领取统计报表','url'=>'a_giftbag/gift/report'),           
    ),
    'extra_node'=>array(
        array('name'=>'全部礼包模块','url'=>'a_giftbag/*')        
    )
);