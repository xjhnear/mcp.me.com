<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'a_game',
    'module_alias' => Lang::get('description.top_mn_game'),
    'module_icon'  => 'android',
    'default_url'=>'a_game/games/search',
    'child_menu' => array(
        array('name'=>Lang::get('description.lm_yxk_yxgl'),'url'=>'a_game/games/search'),
        array('name'=>'待审核','url'=>'a_game/pkg/wait'),
        array('name'=>'每日下载统计','url'=>'a_game/games/download-report'),
        array('name'=>'安卓游戏运营平台','url'=>'a_game/platform/index'),
        //array('name'=>Lang::get('description.lm_yxk_yxtj'),'url'=>'a_game/games/game-add'),              
        array('name'=>'开测表','url'=>'a_game/premiere/list'),
        array('name'=>'经典必玩','url'=>'a_game/mustplay/list'),
    ),
    'extra_node'=>array(  
        array('name'=>'全部游戏模块','url'=>'a_game/*'),
        array('name'=>'游戏API模块','url'=>'a_game/api/*'),      
    )
);