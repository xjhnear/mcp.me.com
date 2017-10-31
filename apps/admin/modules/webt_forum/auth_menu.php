<?php
return array(
    'module_name'  => 'webt_forum',
    'module_alias' => 'V4论坛(提审)',
    'module_icon'  => 'ios',
    'default_url'=>'webt_forum/forum/forum-list',
    'child_menu' => array(
        array('name'=>'论坛列表','url'=>'webt_forum/forum/forum-list'),
        array('name'=>'帖子列表','url'=>'webt_forum/topic/bbs-search'),
        array('name'=>'版主申请','url'=>'webt_forum/forum/master-application-list'),
        array('name'=>'版主招募','url'=>'webt_forum/forum/recruit-rule-list'),
        array('name'=>'热门论坛列表','url'=>'webt_forum/forum/hot-forum-list'),
        array('name'=>'精华帖/悬赏帖','url'=>'webt_forum/topic/set-topic-prize'),
    ),
    'extra_node'=>array(   
        array('name'=>'全部论坛模块权限','url'=>'webt_forum/*')
    )
);