<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'v4_giftbag',
    'module_alias' => 'v4礼包管理',
    'module_icon'  => 'ios',
    'default_url'=>'v4giftbag/package/search',
    'child_menu' => array(
        array('name'=>'礼包库列表','url'=>'v4giftbag/package/search'),
        array('name'=>Lang::get('description.lm_lb_lblb'),'url'=>'v4giftbag/gift/search'),
        array('name'=>'实物库列表','url'=>'v4giftbag/package/search-material'),
        //array('name'=>'礼包活动列表','url'=>'v4giftbag/giftactivity/search'),
    ),
    'extra_node'=>array(
        array('name'=>'全部礼包模块','url'=>'v4giftbag/*'),
    )
);