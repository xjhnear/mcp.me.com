<?php
return array(
    'module_name'  => 'topic',
    'module_alias' => '专题合集',
    'default_url'=>'topic/topicinfo/list',
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
        array('name'=>'专题合集列表','url'=>'topic/topicinfo/list'),
        
                
    ),
    'extra_node'=>array(    
        array('name'=>'全部专题模块权限','url'=>'topic/*')    
    )
);