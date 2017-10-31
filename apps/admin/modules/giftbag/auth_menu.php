<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'giftbag',
    'module_alias' => Lang::get('description.top_mn_giftbag'),
    'module_icon'  => 'ios',
    'default_url'=>'giftbag/gift/search',
    'child_menu' => array(
        array('name'=>Lang::get('description.lm_lb_lblb'),'url'=>'giftbag/gift/search'),
        array('name'=>Lang::get('description.lm_lb_tjlb'),'url'=>'giftbag/gift/add'),
        array('name'=>'领取统计报表','url'=>'giftbag/gift/report'),             
    ),
    'extra_node'=>array(
        array('name'=>'全部礼包模块','url'=>'giftbag/*'),
        array('name'=>'查看礼包列表','url'=>'giftbag/gift/search'),
        array('name'=>'查看礼包信息','url'=>'giftbag/gift/edit'),
        array('name'=>'编辑礼包信息','url'=>'giftbag/gift/save'),
        array('name'=>'发送礼包推送信息','url'=>'giftbag/gift/push'),
        array('name'=>'设置专属用户','url'=>'giftbag/gift/ajax-appoint-uids'),
        array('name'=>'导入礼包卡','url'=>'giftbag/giftcard/import'),
        array('name'=>'初始化礼包领取队列','url'=>'giftbag/giftcard/init-queue'),
        array('name'=>'查看礼包卡','url'=>'giftbag/giftcard/list'),
        array('name'=>'删除礼包卡','url'=>'giftbag/gift/delete'),
                
    )
);