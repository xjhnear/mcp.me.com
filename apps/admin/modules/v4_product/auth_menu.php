<?php
return array(
    'module_name'  => 'v4_product',
    'module_alias' => 'V4商城管理',
    'module_icon'  => 'ios',
    'default_url'=>'v4product/goods/list',
    'child_menu' => array(
        array('name'=>Lang::get('description.lm_sc_spgl'),'url'=>'v4product/goods/list'),
        array('name'=>Lang::get('description.lm_sc_flgl'),'url'=>'v4product/goods/cate-list'),
        array('name'=>Lang::get('商城标签管理'),'url'=>'v4product/label/list'),
        array('name'=>Lang::get('福利管理'),'url'=>'v4product/welfare/list'),
        array('name'=>Lang::get('订单管理'),'url'=>'v4product/order/list'),
        array('name'=>Lang::get('商城订单模版管理'),'url'=>'v4product/form/list'),
        array('name'=>Lang::get('人民币比率'),'url'=>'v4product/rmb/list'),
     ),
    'extra_node'=>array(     
        array('name'=>'全部商城模块权限','url'=>'v4product/*')
    )
);  