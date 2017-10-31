<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'activity',
    'module_alias' => Lang::get('description.top_mn_activity'),
    'default_url'=>'activity/hunt/list',
    'child_menu' => array(
        array('name'=>Lang::get('description.lm_hd_xbxhd'),'url'=>'activity/hunt/list'),
        array('name'=>Lang::get('description.lm_hd_tjbx'),'url'=>'activity/hunt/add'),
        array('name'=>Lang::get('description.lm_hd_yjwdhd'),'url'=>'activity/event/list/1'),
        array('name'=>Lang::get('description.lm_hd_tjyjwd'),'url'=>'activity/event/add/1'),
        array('name'=>Lang::get('description.lm_hd_lthd'),'url'=>'activity/event/list/2'),
        array('name'=>Lang::get('description.lm_hd_tjlthd'),'url'=>'activity/event/add/2'),
        array('name'=>Lang::get('description.lm_hd_jpgl'),'url'=>'activity/prize/home'),
        array('name'=>'5.1分享活动','url'=>'activity/report/share'),
        array('name'=>'公会管理','url'=>'activity/club/list')            
    ),
    'extra_node'=>array(
        array('name'=>'全部活动模块权限','url'=>'activity/*')        
    )
);