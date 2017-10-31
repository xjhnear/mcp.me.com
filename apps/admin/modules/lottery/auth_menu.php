<?php
return array(
    'module_name'  => 'lottery',
    'module_alias' => '代充抽奖',
    'module_icon'  => 'android',
    'default_url'=>'lottery/inscharge/search',
    'child_menu' => array(
        array('name'=>'活动列表','url'=>'lottery/inscharge/activity-search'),
        array('name'=>'抽奖列表','url'=>'lottery/inscharge/lottery-search'),
        array('name'=>'奖项列表','url'=>'lottery/inscharge/prize-search'),
        array('name'=>'表单推送','url'=>'lottery/inscharge/form-push'),
        array('name'=>'代充列表','url'=>'lottery/inscharge/form-list'),
    ),
    'extra_node'=>array(   
        array('name'=>'全部代充模块权限','url'=>'lottery/*')
    )
);