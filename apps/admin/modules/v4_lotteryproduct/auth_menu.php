<?php
return array(
    'module_name'  => 'v4_lotteryproduct',
    'module_alias' => 'v4彩票管理',
    'module_icon'  => 'ios',
    'default_url'=>'v4lotteryproduct/lottery/list',
    'child_menu' => array(
        array('name'=>'天天彩','url'=>'v4lotteryproduct/lottery/list'),
        array('name'=>'大转盘发布/编辑方案','url'=>'v4lotteryproduct/bigwheel/list'),
        array('name'=>'大转盘管理','url'=>'v4lotteryproduct/bigwheel/supervise'),
    ),
    'extra_node'=>array(
        array('name'=>'全部彩票模块','url'=>'v4lotteryproduct/*'),
    )
);