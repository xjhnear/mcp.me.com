<?php
return array(
    'module_name'  => 'v4a_lotteryproduct',
    'module_alias' => 'v4彩票管理',
    'module_icon'  => 'android',
    'default_url'=>'v4alotteryproduct/bigwheel/list',
    'child_menu' => array(
        //array('name'=>'天天彩','url'=>'v4lotteryproduct/lottery/list'),
        //array('name'=>'天天彩玩法说明','url'=>'v4alotteryproduct/lottery/list'),
        array('name'=>'大转盘发布/编辑方案','url'=>'v4alotteryproduct/bigwheel/list'),
        array('name'=>'大转盘管理','url'=>'v4alotteryproduct/bigwheel/supervise'),
        array('name'=>'中奖奖励','url'=>'v4alotteryproduct/rewards/list'),
        array('name'=>'新用户活动时间段','url'=>'v4alotteryproduct/bigwheel/update-config'),
    ),
    'extra_node'=>array(
        array('name'=>'全部彩票模块','url'=>'v4alotteryproduct/*'),
    )
);