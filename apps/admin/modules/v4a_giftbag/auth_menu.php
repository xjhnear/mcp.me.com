<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'v4a_giftbag',
    'module_alias' => 'v4a礼包管理',
    'module_icon'  => 'android',
    'default_url'=>'v4agiftbag/gift/search',
    'child_menu' => array(
        array('name'=>'礼包库列表','url'=>'v4agiftbag/package/search'),
        array('name'=>'实物库列表','url'=>'v4agiftbag/package/search-material'),
        array('name'=>Lang::get('description.lm_lb_lblb'),'url'=>'v4agiftbag/gift/search'),
        array('name'=>'领取统计报表','url'=>'v4agiftbag/report/list'),
        array('name'=>'礼包预约统计表','url'=>'v4agiftbag/booking/list'),
        //array('name'=>'礼包活动列表','url'=>'v4giftbag/giftactivity/search'),
    ),
    'extra_node'=>array(
        array('name'=>'全部礼包模块','url'=>'v4agiftbag/*'),
    )
);