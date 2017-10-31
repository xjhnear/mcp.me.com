<?php
return array(
    'module_name'  => 'comment',
    'module_alias' => Lang::get('description.top_mn_comment'),
    'default_url'=>'comment/comments/index',
    'child_menu' => array(
        array('name'=>Lang::get('description.lm_pl_plgl'),'url'=>'comment/comments/index'),
        array('name'=>Lang::get('description.lm_pl_jbgl'),'url'=>'comment/inform/list'),                
    ),
    'extra_node'=>array(     
        array('name'=>'全部评论模块权限','url'=>'comment/*')   
    )
);