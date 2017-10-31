<?php
return array(
    'module_name'  => 'shop',
    'module_alias' => Lang::get('description.top_mn_shop'),
    'default_url'=>'shop/goods/list',
    'child_menu' => array(
        array('name'=>Lang::get('description.lm_sc_spgl'),'url'=>'shop/goods/list'),
        array('name'=>Lang::get('description.lm_sc_flgl'),'url'=>'shop/goods/cate-list'),
        array('name'=>Lang::get('description.lm_sc_tjsp'),'url'=>'shop/goods/add'),
        array('name'=>Lang::get('description.lm_sc_dhgl'),'url'=>'shop/exchange/list'),
        array('name'=>Lang::get('description.lm_sc_xyt'),'url'=>'shop/goods/rule'),            
    ),
    'extra_node'=>array(     
        array('name'=>'全部商城模块权限','url'=>'shop/*')   
    )
);