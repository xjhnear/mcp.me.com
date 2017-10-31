<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'system',
    'module_alias' => Lang::get('description.top_mn_system'),
    'module_icon'  => 'ios',
    'default_url'=>'system/setting/index',
    'child_menu' => array(
        array('name'=>Lang::get('description.lm_xt_wzsz'),'url'=>'system/setting/index'),
        array('name'=>Lang::get('description.lm_xt_bbgl'),'url'=>'system/setting/app-version-list'),
        array('name'=>Lang::get('description.lm_xt_jfsz'),'url'=>'system/credit/index'),                
        array('name'=>Lang::get('description.lm_xt_jfdj'),'url'=>'system/grade/index'),
        array('name'=>Lang::get('description.lm_xt_tzsz'),'url'=>'system/notice/index'),
        array('name'=>Lang::get('description.lm_xt_rwgl'),'url'=>'system/task/index'),
        array('name'=>Lang::get('description.lm_xt_qdsz'),'url'=>'system/task/checkin'),
        array('name'=>Lang::get('description.lm_xt_tgsz'),'url'=>'system/task/tuiguang'),
        array('name'=>Lang::get('description.lm_xt_qdcs'),'url'=>'system/task/edit-checkin'),
        array('name'=>Lang::get('description.lm_xt_sytpsz'),'url'=>'system/picture/config'),     
        array('name'=>'分享模板管理','url'=>'system/share/index'),        
    ),
    'extra_node'=>array(
        array('name'=>'全部系统模块权限','url'=>'system/*'),
        array('name'=>'设置管理','url'=>'system/setting/*'),
        array('name'=>'任务管理','url'=>'system/task/*'),
        array('name'=>'图片设置','url'=>'system/picture/*'),     
        array('name'=>'积分设置','url'=>'system/credit/*'),   
    )
);