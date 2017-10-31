<?php
return array(
    'module_name'  => 'product',
    'module_alias' => Lang::get('description.top_mn_shop'),
    //'module_alias' => '新商城',
    'default_url'=>'product/goods/list',
    'child_menu' => array(
        array('name'=>Lang::get('description.lm_sc_spgl'),'url'=>'product/goods/list'),
        array('name'=>Lang::get('description.lm_sc_flgl'),'url'=>'product/goods/cate-list'),
    	array('name'=>Lang::get('description.lm_sc_ddgl'),'url'=>'product/goods/product-order-list'),
    	array('name'=>Lang::get('description.lm_sc_sphdgl'),'url'=>'product/goods/product-activity-list'),
        array('name'=>Lang::get('description.lm_sc_tjsp'),'url'=>'product/goods/product-add-edit'),
       //array('name'=>Lang::get('description.lm_sc_dhgl'),'url'=>'product/exchange/list'),
        array('name'=>Lang::get('description.lm_sc_xyt'),'url'=>'product/goods/rule'),
        array('name'=>'卡密管理','url'=>'product/goods/card-list')
    ),
    'extra_node'=>array(     
        array('name'=>'全部商城模块权限','url'=>'product/*')   
    )
);