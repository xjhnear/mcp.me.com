<?php
return array(
    'module_name'  => 'duang',
    'module_alias' => 'Android活动',
    'default_url'=>'duang/activity/index',
    'child_menu' => array(
        array('name'=>'Duang分享','url'=>'duang/activity/list'),
        array('name'=>'图片管理','url'=>'duang/pic/list'),
        array('name'=>'变种分享','url'=>'duang/variation/list'),
        array('name'=>'展示列表','url'=>'duang/show/list'),
        array('name'=>'礼包仓库','url'=>'duang/gbdepot/list'),
    ),
    'extra_node'=>array(       
        array('name'=>'全部用户模块权限','url'=>'duang/*'),
    )
);