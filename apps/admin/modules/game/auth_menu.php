<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'game',
    'module_alias' => Lang::get('description.top_mn_game'),
    'module_icon'  => 'ios',
    'default_url'=>'game/games/search',
    'child_menu' => array(
        array('name'=>Lang::get('description.lm_yxk_yxgl'),'url'=>'game/games/search'),
        array('name'=>Lang::get('description.lm_yxk_yxtj'),'url'=>'game/games/game-add'),
        array('name'=>'数据统计','url'=>'game/data/search'),
        array('name'=>'月度统计','url'=>'game/data/game-map'),
        array('name'=>'数据统计(实)','url'=>'game/data/real-search'),
        array('name'=>'月度统计(实)','url'=>'game/data/real-game-map'),              
    ),
    'extra_node'=>array(  
        array('name'=>'全部游戏模块','url'=>'game/*'),
        array('name'=>'游戏API模块','url'=>'game/api/*'),      
    )
);