<?php
return array(
    'module_name'  => 'v4a_product',
    'module_alias' => 'v4商城',
    'module_icon'  => 'android',
    //'module_alias' => '新商城',
    'default_url'=>'v4aproduct/goods/list',
    'child_menu' => array(
        array('name'=>Lang::get('description.lm_sc_spgl'),'url'=>'v4aproduct/goods/list'),
        array('name'=>Lang::get('description.lm_sc_flgl'),'url'=>'v4aproduct/goods/cate-list'),
    	array('name'=>Lang::get('description.lm_sc_ddgl'),'url'=>'v4aproduct/goods/product-order-list'),
    	array('name'=>Lang::get('description.lm_sc_sphdgl'),'url'=>'v4aproduct/goods/product-activity-list'),
        array('name'=>Lang::get('description.lm_sc_tjsp'),'url'=>'v4aproduct/goods/product-add-edit'),
       //array('name'=>Lang::get('description.lm_sc_dhgl'),'url'=>'product/exchange/list'),
        array('name'=>Lang::get('description.lm_sc_xyt'),'url'=>'v4aproduct/goods/rule'),
        array('name'=>'卡密管理','url'=>'v4aproduct/goods/card-list'),
        array('name'=>'商城数据导出','url'=>'v4aproduct/goods/product-data-list'),
        array('name'=>'商城表单列表','url'=>'v4aproduct/goods/form'),
        array('name'=>'商城推荐位','url'=>'v4aproduct/goods/recommend'),
        array('name'=>'商城交易记录查询','url'=>'v4aproduct/goods/query'),
        array('name'=>'需要处理的订单','url'=>'v4aproduct/goods/need-order-list'),
        array('name'=>'实物奖励查询','url'=>'v4alotteryproduct/rewards/list'),
    ),
    'extra_node'=>array(     
        array('name'=>'全部商城模块权限','url'=>'v4aproduct/*')
    )
);