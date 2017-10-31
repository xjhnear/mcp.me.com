<?php
return array(
    'module_name'  => 'xgame',
    'module_alias' => Lang::get('description.top_mn_xgame'),
    'default_url'=>'xgame/forum/add',
    'child_menu' => array(
        array('name'=>Lang::get('description.lm_xyx_tj'),'url'=>'xgame/forum/add'),
        array('name'=>Lang::get('description.lm_xyx_ck'),'url'=>'xgame/forum/index'),
        array('name'=>Lang::get('description.lm_xyx_ck').'banner','url'=>'xgame/forum/banner-list'),
    	array('name'=>Lang::get('description.lm_xyx_count'),'url'=>'xgame/forum/xgame-count'),
    ),
    'extra_node'=>array(  
        array('name'=>'全部小游戏模块','url'=>'xgame/*')      
    )
);