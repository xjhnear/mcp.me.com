<?php
return array(
    'module_name'  => 'adv',
    'module_alias' => Lang::get('description.top_mn_adv'),
    'default_url'=>'adv/apptypes/list',
    'child_menu' => array(
        /***
        array('name'=>Lang::get('description.lm_gg_yxxzybjl'),'url'=>'adv/credit/index'),
        array('name'=>Lang::get('description.lm_gg_sylbgg'),'url'=>'adv/ads/list/1'),  
        array('name'=>Lang::get('description.lm_gg_syggt'),'url'=>'adv/ads/list/8'),
        array('name'=>Lang::get('description.lm_gg_sytcgg'),'url'=>'adv/ads/list/6'),
        array('name'=>Lang::get('description.lm_gg_rmyxtj'),'url'=>'adv/ads/list/2'),
        array('name'=>Lang::get('description.lm_gg_yxxqytcgg'),'url'=>'adv/ads/list/3'),
        array('name'=>Lang::get('description.lm_gg_yxxqyxzangg'),'url'=>'adv/ads/list/4'),
        array('name'=>Lang::get('description.lm_gg_yxxqycnxhgg'),'url'=>'adv/ads/list/7'),
        array('name'=>Lang::get('description.lm_gg_qdygg'),'url'=>'adv/ads/list/5'), 
        ***/
        array('name'=>'系统与版本管理','url'=>'adv/apptypes/list'),
        array('name'=>'推荐位类型管理','url'=>'adv/recommend/list'), 
        array('name'=>'广告位置管理','url'=>'adv/location/list'),
        array('name'=>'广告类型管理','url'=>'adv/nature/list'),
        array('name'=>'广告发布管理','url'=>'adv/releaseadv/list'),   
        array('name'=>'推荐发布管理','url'=>'adv/recommendedadv/list'),               
    ),
    'extra_node'=>array(    
        array('name'=>'全部广告模块权限','url'=>'adv/*')    
    )
);