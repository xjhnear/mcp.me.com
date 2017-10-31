<?php
return array(
    'module_name'  => 'web_forum',
    'module_alias' => 'V4论坛',
    'module_icon'  => 'ios',
    'default_url'=>'web_forum/bbs/search',
    'child_menu' => array(
        array('name'=>'论坛列表','url'=>'web_forum/forum/forum-list'),
        array('name'=>'帖子列表','url'=>'web_forum/topic/bbs-search'),
    ),
    'extra_node'=>array(   
        array('name'=>'全部论坛模块权限','url'=>'web_forum/*')
    )
);