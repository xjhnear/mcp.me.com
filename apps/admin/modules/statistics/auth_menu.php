<?php
use Illuminate\Support\Facades\Lang;
return array(
    'module_name'  => 'statistics',
    'module_alias' => Lang::get('description.top_mn_statistics'),
    'default_url'=>'statistics/tuiguang/index',
    'child_menu' => array(
    	array('name'=>Lang::get('description.lm_tj_tgtj'), 'url'=>'statistics/tuiguang/index'),
        array('name'=>Lang::get('description.lm_tj_yb'),'url'=>'statistics/rank/index'),
        array('name'=>Lang::get('description.lm_tj_yhyb'),'url'=>'statistics/usercredit/index'),
        array('name'=>Lang::get('description.lm_tj_lt'),'url'=>'statistics/forum/index'),
        array('name'=>'广告效果统计','url'=>'statistics/adv/user'),
        array('name'=>'监控渠道管理','url'=>'statistics/monitor/channel-list'),
        array('name'=>'监控配置管理','url'=>'statistics/monitor/config-list'),
        
    ),
    'extra_node'=>array( 
        array('name'=>'全部统计模块权限','url'=>'statistics/*')       
    )
);