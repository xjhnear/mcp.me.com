<?php
return array(
    'module_name'  => 'forum',
    'module_alias' => Lang::get('description.top_mn_miniforum'),
    'default_url'=>'forum/topic/search',
    'child_menu' => array(
        array('name'=>Lang::get('description.lm_wlt_tzgl'),'url'=>'forum/topic/search'),
        array('name'=>Lang::get('description.lm_wlt_fxt'),'url'=>'forum/topic/add'),
        array('name'=>Lang::get('description.lm_wlt_gggl'),'url'=>'forum/notice/list'),
        array('name'=>Lang::get('description.lm_wlt_jbgl'),'url'=>'forum/inform/list'),
        array('name'=>Lang::get('description.lm_wlt_ykflt'),'url'=>'forum/game/search'),
        array('name'=>Lang::get('description.lm_wlt_xyyzd'),'url'=>'forum/channel/expedition-list'),
        array('name'=>Lang::get('description.lm_wlt_sdybxyyzd'),'url'=>'forum/channel/list?game_id=2'),
        array('name'=>Lang::get('description.lm_wlt_jfgz'),'url'=>'forum/topic/rule'),
    ),
    'extra_node'=>array(   
        array('name'=>'全部论坛模块权限','url'=>'forum/*')     
    )
);