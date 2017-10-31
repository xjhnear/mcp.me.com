<?php
$config = require __DIR__ . '/../../../config/app.php';
$config['installed_modules'] = array(
    'activity'=>app_path() . '/modules/activity',
    'adv'=>app_path() . '/modules/adv',
    'article'=>app_path() . '/modules/article',
    'comment'=>app_path() . '/modules/comment',
    'forum'=>app_path() . '/modules/forum',
    'game'=>app_path() . '/modules/game',
    'giftbag'=>app_path() . '/modules/giftbag',
    'message'=>app_path() . '/modules/message',
    'feedback'=>app_path() . '/modules/feedback',
    'system'=>app_path() . '/modules/system',
    'shop'=>app_path() . '/modules/shop',
    'user'=>app_path() . '/modules/user',
	'admin'=>app_path() . '/modules/admin',    
	'statistics'=>app_path() . '/modules/statistics',  
	'xgame'=>app_path() . '/modules/xgame',
);
return $config;
