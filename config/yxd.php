<?php
return array(
	//推广任务的消息通知
	'task_message'=>array('newtuiguang_1'=>'新用户注册填写推广ID奖励[text]个游币', 
						'oldtuiguang_1'=>'老用户每推广一名新用户奖励[text]个游币', 
						'oldtuiguang_10'=>'老用户每推广10名新用户奖励[text]个游币', 
						'oldtuiguang_100'=>'老用户每推广100名新用户奖励[text]个游币',
						'oldtuiguang_500'=>'老用户每推广500名新用户奖励[text]个游币', 
						'oldtuiguang_1000'=>'老用户每推广1000名新用户奖励[text]个游币', ),
    'task'=>array('tasktype'=>array('1'=>'每日任务','2'=>'新手任务','3'=>'推广任务')),
	
    'circle_feedtype'=>array('topic'=>'发帖','comment'=>'评论'),

    'user_feedtype'=>array('topic'=>'发帖','reply'=>'回帖','game_comment'=>'游戏评论','article_comment'=>'文章评论','gift'=>'领取礼包','activity'=>'参与活动'),
    //评论类型
    'comment_type'=>array('0'=>'yxd_forum_topic','6'=>'m_games','1'=>'m_news','2'=>'m_gonglue','3'=>'m_feedback','4'=>'m_game_notice','5'=>'m_videos','7'=>'m_xyx_game'),

    'forum_global_channel'=>array('1'=>'新人报道','2'=>'游戏问答','3'=>'游戏秀','4'=>'系统区'),

    'like_type'=>array('0'=>'topic','6'=>'m_games','1'=>'m_news','2'=>'m_gonglue','3'=>'m_feedback','4'=>'m_game_notice','5'=>'m_videos','7'=>'m_xyx_game'),

    'game_pricetype'=>array('1'=>'免费','2'=>'限免','3'=>'收费'),

    'game_zonetype'=>array('0'=>'普通专区','1'=>'精品专区'),

    'credit_history_tpl'=>array(
        'login'=>'',
    ),
    'applist'=>array(
        ''=>'游戏多',
        'yxdjqb'=>'超人版',
        'yxdandroid'=>'游戏多android'
    ),
    'platform_list'=>array(
        '1'=>'360手机助手',
        '2'=>'口袋巴士',
        '3'=>'腾讯应用宝',
        '4'=>'百度手机助手',
        '5'=>'当乐游戏',
        '6'=>'官方下载',
        '7'=>'UC九游',
        '8'=>'91助手',
        '13'=>'小米商店',
        '15'=>'魔方网',
    ),
    
    'app_version'=>array(
        '2.0.0'=>'2.0.0',
        '2.0.1'=>'2.0.1',
        '2.1.0'=>'2.1.0',
        '2.2.0'=>'2.2.0',
        '3.0.0'=>'3.0.0'
    ),
    'advtype'=>array(
        '1'=>'首页轮播广告',
        '2'=>'热门游戏推荐',
        '3'=>'游戏详情页弹窗广告',
        '4'=>'游戏详情页下载按钮广告',
        '5'=>'启动页广告',
        '6'=>'首页弹窗广告',
        '7'=>'游戏详情页猜你喜欢广告',
        '8'=>'首页广告条',
    ),
    'adv' => array(
        'INDEX_LUNBO_ADV' => 1,
        'INDEX_LIST_ADV' => 2,
        'GAME_PIC_ADV' => 3,
        'GAME_DETAIL_DOWN_ADV' => 4,
        'APP_START_ADV' => 5,
        'INDEX_PIC_ADV' => 6
    ),
    'at_me'=>array('0'=>'yxd_forum_thread'),
    'discovery_tags'=>array(
		array('tag'=>'跑酷', 'name' => '跑酷'),
		array('tag'=>'益智', 'name'=>'益智'),
		array('tag'=>'物理', 'name'=>'物理'),
		array('tag'=>'找茬', 'name'=>'找茬'),
		array('tag'=>'重口味', 'name'=>'重口味'),
		array('tag'=>'萝卜', 'name'=>'萝卜'),
		array('tag'=>'水果', 'name'=>'水果'),
		array('tag'=>'小鸟', 'name'=>'小鸟'),
		array('tag'=>'僵尸', 'name'=>'僵尸'),
		array('tag'=>'MT', 'name'=>'MT'),
		array('tag'=>'武侠', 'name'=>'武侠'),
		array('tag'=>'卡牌', 'name'=>'卡牌'),
		array('tag'=>'三国', 'name'=>'三国'),
		array('tag'=>'塔防', 'name'=>'作战塔防'),
		array('tag'=>'现代战争', 'name'=>'现代战争'),
		array('tag'=>'电影改编', 'name'=>'电影改编'),
		array('tag'=>'无尽之剑', 'name'=>'无尽之剑'),
		array('tag'=>'异形', 'name'=>'异形'),
		array('tag'=>'恋爱', 'name'=>'恋爱'),
		array('tag'=>'极品飞车', 'name'=>'极品飞车'),
		array('tag'=>'真实赛车', 'name'=>'真实赛车'),
		array('tag'=>'极品飞车', 'name'=>'极品飞车'),
		array('tag'=>'模拟人生', 'name'=>'模拟人生'),
		array('tag'=>'老虎机', 'name'=>'老虎机'),
		array('tag'=>'三国杀', 'name'=>'三国杀'),
		array('tag'=>'斗地主', 'name'=>'斗地主'),
		array('tag'=>'街机', 'name'=>'街机'),
		array('tag'=>'暴力', 'name'=>'暴力'),
		array('tag'=>'仙剑', 'name'=>'仙剑'),
		array('tag'=>'最终幻想', 'name'=>'最终幻想'),
		array('tag'=>'高尔夫', 'name'=>'高尔夫'),
		array('tag'=>'初音未来', 'name'=>'初音未来'),
		array('tag'=>'足球', 'name'=>'足球'),
		array('tag'=>'恋爱', 'name'=>'恋爱'),
		array('tag'=>'密室', 'name'=>'密室'),
		array('tag'=>'魔兽', 'name'=>'魔兽'),
		array('tag'=>'卡丁车', 'name'=>'卡丁车'),
	),
    'charge_form'=>array(
        'gname_1' => '游戏名',
        'gchannel_2' => '渠道',
        'gamezone_3' => '游戏区服',
        'gameAccount_4' => '游戏登录账号',
        'gamePassword_5' => '游戏登录密码',
        'roleName_6' => '角色名',
        'qq_7' => 'QQ号',
        'mobile_8' => '手机号',
        'address_9' => '收货地址'
    ),
    //需要过滤替换的关键字字库（多个词用‘,’分开，字符前后请不要有空格）
    'filter_chars'=>'淘宝,内购,破解,兼职,同步推,同步,步推,91助手,PP,快用,助手,当乐,taobao,tongbutui,itools',
    //替换后显示的字符
    'replace_chars' => '',
    'baidu_tags' => array(
       'reserve_giftbag' => 'reserve_giftbag_', //预约礼包

    )
);