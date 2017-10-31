<?php
$v3_tables = require 'dbtable_v3.php';
$v4_tables = array(
    'youxiduo_config_model_version'=>array('db'=>'cms','table'=>'version'),
    
    'youxiduo_android_model_appadv'=>array('db'=>'cms','table'=>'appadv'),
    'youxiduo_android_model_adv'=>array('db'=>'cms','table'=>'adv'),
    'youxiduo_android_model_advpos'=>array('db'=>'cms','table'=>'advpos'),
    'youxiduo_android_model_game'=>array('db'=>'cms','table'=>'games_apk'),
    'youxiduo_android_model_gameplat'=>array('db'=>'cms','table'=>'games_apkplat'),
    'youxiduo_android_model_gamevideo'=>array('db'=>'cms','table'=>'games_video'),
    'youxiduo_android_model_gamemustplay'=>array('db'=>'cms','table'=>'game_mustplay'),
    'youxiduo_android_model_gamecollect'=>array('db'=>'cms','table'=>'zt'),
    'youxiduo_android_model_gamecollectgames'=>array('db'=>'cms','table'=>'zt_games'),
    'youxiduo_android_model_news'=>array('db'=>'cms','table'=>'news'),
    'youxiduo_android_model_guide'=>array('db'=>'cms','table'=>'gonglue'),
    'youxiduo_android_model_opinion'=>array('db'=>'cms','table'=>'feedback'),
    'youxiduo_android_model_video'=>array('db'=>'cms','table'=>'videos'),
    'youxiduo_android_model_videogame'=>array('db'=>'cms','table'=>'videos_games'), 
    'youxiduo_android_model_article'=>array('db'=>'cms','table'=>'article'),
    'youxiduo_game_model_games'=>array('db'=>'cms','table'=>'games'),

    'youxiduo_android_model_gametool'=>array('db'=>'cms','table'=>'games_map'),
    'youxiduo_android_model_gamerecommend'=>array('db'=>'cms','table'=>'game_recommend'),
    'youxiduo_android_model_newgame'=>array('db'=>'cms','table'=>'game_notice'),
    'youxiduo_android_model_gametype'=>array('db'=>'cms','table'=>'game_type'),
    'youxiduo_android_model_tag'=>array('db'=>'cms','table'=>'tag'),
    'youxiduo_android_model_recommend'=>array('db'=>'cms','table'=>'recommend'),
    'youxiduo_android_model_comment'=>array('db'=>'cms','table'=>'comment'),
    'youxiduo_android_model_activity'=>array('db'=>'cms','table'=>'hot_activity'),
    'youxiduo_android_model_gamepicture'=>array('db'=>'android','table'=>'game_picture'),
    //'youxiduo_android_model_comment'=>array('db'=>'cms','table'=>'comment'),

    'youxiduo_android_model_gamepackage'=>array('db'=>'cms','table'=>'apk_package_info'),
    'youxiduo_android_model_userpackage'=>array('db'=>'cms','table'=>'apk_not_match'),
    'youxiduo_android_model_gamepackagecollect'=>array('db'=>'cms','table'=>'android_app_info'),
    'youxiduo_android_model_gamepackagematchhistory'=>array('db'=>'android','table'=>'apk_match_history'),
    'youxiduo_android_model_devicepackage'=>array('db'=>'cms','table'=>'device_package'),
    'youxiduo_android_model_usergame'=>array('db'=>'cms','table'=>'user_games'),
    'youxiduo_android_model_userfavorite'=>array('db'=>'cms','table'=>'user_favorite'),
    //'youxiduo_user_model_accountmobile'=>array('db'=>'club','table'=>'account_mobile_history'),

    'youxiduo_zhibo_model_zhibogame'=>array('db'=>'cms','table'=>'zhibo_game'),         //直播游戏
    'youxiduo_zhibo_model_zhiboguest'=>array('db'=>'cms','table'=>'zhibo_guest'),       //直播嘉宾/主播
    'youxiduo_zhibo_model_zhiboplat'=>array('db'=>'cms','table'=>'zhibo_plat'),         //直播平台

    'youxiduo_android_model_userdevice'=>array('db'=>'android','table'=>'account_device'),
    'youxiduo_user_model_account'=>array('db'=>'club','table'=>'account'),
    'youxiduo_user_model_accountsession'=>array('db'=>'club','table'=>'account_session'),
    //Android
    'youxiduo_android_model_creditaccount'=>array('db'=>'android','table'=>'credit_account'),
    'youxiduo_android_model_creditlevel'=>array('db'=>'android','table'=>'credit_level'),
    'youxiduo_android_model_giftbag'=>array('db'=>'android','table'=>'giftbag'),
    'youxiduo_android_model_giftbagaccount'=>array('db'=>'android','table'=>'giftbag_account'),
    'youxiduo_android_model_giftbagappoint'=>array('db'=>'android','table'=>'giftbag_appoint'),
    'youxiduo_android_model_giftbagcard'=>array('db'=>'android','table'=>'giftbag_card'),
    'youxiduo_android_model_gameplatform'=>array('db'=>'android','table'=>'game_platform'),
    'youxiduo_android_model_reserve'=>array('db'=>'android','table'=>'giftbag_reserve'),
    'youxiduo_android_model_checkinfo'=>array('db'=>'android','table'=>'checkinfo'),
    'youxiduo_message_model_messagetpl'=>array('db'=>'android','table'=>'system_message_tpl'),
    'youxiduo_message_model_messagetype'=>array('db'=>'android','table'=>'system_message_type'),
    'youxiduo_android_model_gamedownloadflow'=>array('db'=>'android','table'=>'game_download_flow'),

    'youxiduo_android_model_task'=>array('db'=>'android','table'=>'task'),
    'youxiduo_android_model_taskaccount'=>array('db'=>'android','table'=>'task_account'),
    'youxiduo_android_model_sharelimit'=>array('db'=>'android','table'=>'share_limit'),

    'youxiduo_android_model_activitysharehistory'=>array('db'=>'android','table'=>'activity_share_history'),
    'youxiduo_android_model_activitytask'=>array('db'=>'android','table'=>'activity_task'),
    'youxiduo_android_model_activitytaskuser'=>array('db'=>'android','table'=>'activity_task_user'),
    'youxiduo_android_model_activitytaskuserscreenshot'=>array('db'=>'android','table'=>'activity_task_user_screenshot'),
    'youxiduo_android_model_checkinstask'=>array('db'=>'android','table'=>'checkins_task'),
    'youxiduo_android_model_checkinstaskuser'=>array('db'=>'android','table'=>'checkins_task_user'),
    'youxiduo_android_model_advapplink'=>array('db'=>'android','table'=>'adv_app_link'),
    'youxiduo_android_model_advimage'=>array('db'=>'android','table'=>'adv_image'),
    'youxiduo_android_model_advlocation'=>array('db'=>'android','table'=>'adv_location'),
    
    'youxiduo_user_model_usermobile'=>array('db'=>'cms','table'=>'user_mobile'),
    'youxiduo_user_model_mobilesmshistory'=>array('db'=>'cms','table'=>'mobile_sms_history'),

    'youxiduo_v4_user_model_relation'=>array('db'=>'club','table'=>'account_follow'),
    'youxiduo_v4_user_model_area'=>array('db'=>'club','table'=>'area'),
    'youxiduo_v4_user_model_userarea'=>array('db'=>'club','table'=>'account_area'),
    'youxiduo_v4_user_model_mobileblacklist'=>array('db'=>'club','table'=>'mobile_blacklist'),
    'youxiduo_v4_user_model_loginlimit'=>array('db'=>'club','table'=>'login_limit'),
    'youxiduo_v4_user_model_thirdaccountlogin'=>array('db'=>'club','table'=>'account_thirdlogin'),
    'youxiduo_v4_game_model_androidgame'=>array('db'=>'cms','table'=>'games_apk'),
    'youxiduo_v4_game_model_iosgame'=>array('db'=>'cms','table'=>'games'),
    'youxiduo_v4_game_model_iosgameschemes'=>array('db'=>'cms','table'=>'games_schemes'),
    'youxiduo_v4_game_model_game'=>array('db'=>'cms','table'=>'game'),
    'youxiduo_v4_game_model_gamearea'=>array('db'=>'club','table'=>'game_area'),
    //'youxiduo_v4_game_model_gamecircle'=>array('db'=>'club','table'=>'account_circle'),
    'youxiduo_v4_user_model_userverifycode'=>array('db'=>'club','table'=>'account_verifycode'),
 
    'youxiduo_v4_game_model_gamemustplay'=>array('db'=>'cms','table'=>'game_mustplay'),
    'youxiduo_v4_game_model_gamecollect'=>array('db'=>'cms','table'=>'zt'),
    'youxiduo_v4_game_model_gamecollecttype'=>array('db'=>'cms','table'=>'zt_type'),
    'youxiduo_v4_game_model_gamecollectgames'=>array('db'=>'cms','table'=>'zt_games'),
    'youxiduo_v4_game_model_gametype'=>array('db'=>'cms','table'=>'game_type'),
    'youxiduo_v4_game_model_tag'=>array('db'=>'cms','table'=>'tag'),
    'youxiduo_v4_game_model_gametag'=>array('db'=>'cms','table'=>'games_tag'),
    'youxiduo_v4_game_model_gamebeta'=>array('db'=>'cms','table'=>'newgame'),
    'youxiduo_v4_game_model_usergame'=>array('db'=>'club','table'=>'account_circle'),
    'youxiduo_v4_game_model_usergamereserve'=>array('db'=>'club','table'=>'gift_reserve'),

    'youxiduo_v4_cms_model_news'=>array('db'=>'cms','table'=>'news'),
    'youxiduo_v4_cms_model_guide'=>array('db'=>'cms','table'=>'gonglue'),
    'youxiduo_v4_cms_model_opinion'=>array('db'=>'cms','table'=>'feedback'),
    'youxiduo_v4_cms_model_video'=>array('db'=>'cms','table'=>'videos'),
    'youxiduo_v4_cms_model_videogame'=>array('db'=>'cms','table'=>'videos_games'),
    'youxiduo_v4_cms_model_videotype'=>array('db'=>'cms','table'=>'videos_type'), 
    'youxiduo_v4_cms_model_article'=>array('db'=>'cms','table'=>'article'),
    'youxiduo_v4_cms_model_newgame'=>array('db'=>'cms','table'=>'game_notice'),
    
    'youxiduo_v4_common_model_sharetpl'=>array('db'=>'club','table'=>'share_tpl'),
    'youxiduo_v4_common_model_shareadv'=>array('db'=>'club','table'=>'share_adv'),    

    'youxiduo_system_model_admin'=>array('db'=>'cms','table'=>'admin'),
    'youxiduo_system_model_module'=>array('db'=>'system','table'=>'module'),
    'youxiduo_system_model_authgroup'=>array('db'=>'system','table'=>'auth_group'),
    'youxiduo_system_model_appconfig'=>array('db'=>'cms','table'=>'version'),

    'youxiduo_android_model_gametag'=>array('db'=>'cms','table'=>'games_tag'),//新增 游戏标签
    'youxiduo_android_model_commentoperator'=>array('db'=>'cms','table'=>'comment_operator'),//新增
    'youxiduo_android_model_user'=>array('db'=>'cms','table'=>'user'),//新增 用户
    'youxiduo_android_model_zone'=>array('db'=>'cms','table'=>'zone'),//新增 专题
    'youxiduo_android_model_userdisable'=>array('db'=>'cms','table'=>'user_disable'),//新增 禁言用户
    'youxiduo_android_model_systemconfig'=>array('db'=>'cms','table'=>'system_config'),//新增
    'youxiduo_android_model_gamescore'=>array('db'=>'cms','table'=>'game_score'),//新增 游戏评分
    'youxiduo_android_model_systemfeedback'=>array('db'=>'cms','table'=>'system_feedback'),//新增 用户反馈
    'youxiduo_android_model_platform'=>array('db'=>'cms','table'=>'game_platform'),//新增 游戏平台来源
    'youxiduo_android_model_gamefirst'=>array('db'=>'cms','table'=>'newgame'),//新增 开测
    'youxiduo_android_model_gameapkdownload'=>array('db'=>'cms','table'=>'games_apkdownload'),//新增 游戏下载统计
    'youxiduo_android_model_appadvstat'=>array('db'=>'cms','table'=>'appadv_stat'),//新增 广告位
    'youxiduo_android_model_share'=>array('db'=>'cms','table'=>'share'),//新增 分享
    'youxiduo_android_model_taskblacklist'=>array('db'=>'android','table'=>'task_blacklist'),
    

    'youxiduo_cms_model_arctiny'=>array('db'=>'mobile','table'=>'arctiny'),
    'youxiduo_cms_model_arctype'=>array('db'=>'mobile','table'=>'arctype'),
    'youxiduo_cms_model_archives'=>array('db'=>'mobile','table'=>'archives'),
    'youxiduo_cms_model_addonarticle'=>array('db'=>'mobile','table'=>'addonarticle'),
    'youxiduo_cms_model_addongame'=>array('db'=>'mobile','table'=>'addongame'),
    'youxiduo_cms_model_addonimage'=>array('db'=>'mobile','table'=>'addonimage'),
    'youxiduo_cms_model_arctypemodule'=>array('db'=>'mobile','table'=>'arctype_module'),
    'youxiduo_cms_model_arctypemodulerule'=>array('db'=>'mobile','table'=>'arctype_module_rule'),
	'youxiduo_cms_model_arcatt'=>array('db'=>'mobile','table'=>'arcatt'),

    'youxiduo_bbs_model_bbsappend'=>array('db'=>'club','table'=>'bbs_append'),
    'youxiduo_bbs_model_bbshome'=>array('db'=>'club','table'=>'bbs_home'),
    'youxiduo_bbs_model_bbsbanner'=>array('db'=>'club','table'=>'bbs_banner'),
    'youxiduo_bbs_model_bbsrecommend'=>array('db'=>'club','table'=>'bbs_recommend'),
    'youxiduo_bbs_model_bbsgiftbag'=>array('db'=>'club','table'=>'bbs_giftbag'),
	
	'youxiduo_activity_model_duangmain'=>array('db'=>'share_activity','table'=>'duang_main'),
    'youxiduo_activity_model_duanggiftbag'=>array('db'=>'share_activity','table'=>'duang_giftbag'),
    'youxiduo_activity_model_duanggiftbagcard'=>array('db'=>'share_activity','table'=>'duang_giftbag_card'),
    'youxiduo_activity_model_duangsharegiftbagcard'=>array('db'=>'share_activity','table'=>'duang_share_giftbag_card'),
    'youxiduo_activity_model_duangpic'=>array('db'=>'share_activity','table'=>'duang_pic'),

    'youxiduo_activity_model_variation_variationactivity'=>array('db'=>'share_activity','table'=>'variation_activity'),
    'youxiduo_activity_model_variation_variationmain'=>array('db'=>'share_activity','table'=>'variation_main'),
    'youxiduo_activity_model_variation_variationmoney'=>array('db'=>'share_activity','table'=>'variation_money'),
    'youxiduo_activity_model_variation_giftbagdepot'=>array('db'=>'share_activity','table'=>'giftbag_depot'),
    'youxiduo_activity_model_variation_giftbaglist'=>array('db'=>'share_activity','table'=>'giftbag_list'),
    'youxiduo_activity_model_variation_actdeprelate'=>array('db'=>'share_activity','table'=>'act_dep_relate'),
    'youxiduo_activity_model_variation_variationshow'=>array('db'=>'share_activity','table'=>'variation_show'),
    'youxiduo_activity_model_variation_variationselect'=>array('db'=>'share_activity','table'=>'variation_select'),

    'youxiduo_activity_model_union_unionbanner'=>array('db'=>'share_activity','table'=>'union_banner'),

    'youxiduo_activity_model_dcactivity'=>array('db'=>'share_activity','table'=>'dc_activity'),
    'youxiduo_activity_model_dclottery'=>array('db'=>'share_activity','table'=>'dc_lottery'),
    'youxiduo_activity_model_dcprize'=>array('db'=>'share_activity','table'=>'dc_prize'),
    'youxiduo_activity_model_dcjoin'=>array('db'=>'share_activity','table'=>'dc_join'),
    'youxiduo_activity_model_activitycollection'=>array('db'=>'activity','table'=>'activity_collection'),
    'youxiduo_activity_model_activitycollectioninfo'=>array('db'=>'activity','table'=>'activity_collection_info'),
    'youxiduo_activity_model_activitygiftbag'=>array('db'=>'activity','table'=>'activity_giftbag'),
    'youxiduo_activity_model_ttxdgirl'=>array('db'=>'activity','table'=>'ttxd_girl'),
    'youxiduo_activity_model_ttxdvote'=>array('db'=>'activity','table'=>'ttxd_vote'),
    'youxiduo_activity_model_activityblcxcomment'=>array('db'=>'activity','table'=>'activity_blcx_comment'),
    'youxiduo_activity_model_activityblcxpreson'=>array('db'=>'activity','table'=>'activity_blcx_preson'),
    'youxiduo_activity_model_activityblcxvote'=>array('db'=>'activity','table'=>'activity_blcx_vote'),
    'youxiduo_activity_model_activityblcxprize'=>array('db'=>'activity','table'=>'activity_blcx_prize'),
    'youxiduo_activity_model_activityblcxbag'=>array('db'=>'activity','table'=>'activity_blcx_bag'),
    'youxiduo_activity_model_activityblcxinfo'=>array('db'=>'activity','table'=>'activity_blcx_info'),
    'youxiduo_activity_model_cashgame'=>array('db'=>'activity','table'=>'cash_game'),
    'youxiduo_activity_model_chinajoybarrage'=>array('db'=>'activity','table'=>'cj_barrage'),
    'youxiduo_activity_model_chinajoyguide'=>array('db'=>'activity','table'=>'cj_guide'),
    'youxiduo_activity_model_chinajoymanufacturers'=>array('db'=>'activity','table'=>'cj_manufacturers'),
    'youxiduo_activity_model_activitycomment'=>array('db'=>'activity','table'=>'activity_comment'),
    'youxiduo_game_model_gamesapk'=>array('db'=>'cms','table'=>'games_apk'),
	
	'youxiduo_v4_activity_model_channelclick'=>array('db'=>'report','table'=>'CHANNEL_CLICK'),
    'youxiduo_v4_activity_model_downloadchannel'=>array('db'=>'report','table'=>'DOWNLOAD_CHANNEL'),
    'youxiduo_v4_activity_model_statisticconfig'=>array('db'=>'report','table'=>'STATISTIC_CONFIG'),

    'youxiduo_v4_activity_model_club'=>array('db'=>'ios_club','table'=>'club'),
    'youxiduo_v4_activity_model_clubgame'=>array('db'=>'ios_club','table'=>'club_game'),
    //'youxiduo_android_model_gamevideo'=>array('db'=>'cms','table'=>'games_video'),
    //'youxiduo_android_model_gamevideo'=>array('db'=>'cms','table'=>'games_video'),
    //''=>array('db'=>'','table'=>''),
    //''=>array('db'=>'','table'=>''),
);

return array_merge($v4_tables,$v3_tables);
