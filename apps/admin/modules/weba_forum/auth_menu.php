<?php
return array(
    'module_name'  => 'weba_forum',
    'module_alias' => 'V4论坛',
    'module_icon'  => 'android',
    'default_url'=>'weba_forum/topic/forum-list',
    'child_menu' => array(
        array('name'=>'论坛列表','url'=>'weba_forum/topic/forum-list'),
        array('name'=>'社区帖子管理','url'=>'weba_forum/topic/bbs-search'),
        array('name'=>'社区发新贴','url'=>'weba_forum/topic/bbs-add'),
        array('name'=>'帖子限制管理','url'=>'weba_forum/replylimit/list'),
        array('name'=>'热门论坛列表','url'=>'weba_forum/topic/hot-forum-list'),
    ),
    'extra_node'=>array(   
        array('name'=>'全部论坛模块权限','url'=>'weba_forum/*')
    )
);