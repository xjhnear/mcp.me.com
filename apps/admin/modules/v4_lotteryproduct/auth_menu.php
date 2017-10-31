<?php
return array(
    'module_name'  => 'v4_lotteryproduct',
    'module_alias' => 'v4彩票管理',
    'module_icon'  => 'ios',
    'default_url'=>'v4lotteryproduct/lottery/list',
    'child_menu' => array(
        array('name'=>'天天彩','url'=>'v4lotteryproduct/lottery/list'),
        array('name'=>'天天彩玩法说明','url'=>'v4lotteryproduct/explain/add'),
        array('name'=>'大转盘游币数设置','url'=>'v4lotteryproduct/lotteryconfig/add'),
        array('name'=>'大转盘发布/编辑方案','url'=>'v4lotteryproduct/bigwheel/list'),
        array('name'=>'大转盘管理','url'=>'v4lotteryproduct/bigwheel/supervise'),
	array('name'=>'天天彩游币管理','url'=>'v4lotteryproduct/lottery/yb'),
        array('name'=>'大转盘游币管理','url'=>'v4lotteryproduct/lottery/big-yb'),
    ),
    'extra_node'=>array(
        array('name'=>'全部彩票模块','url'=>'v4lotteryproduct/*'),
    )
);