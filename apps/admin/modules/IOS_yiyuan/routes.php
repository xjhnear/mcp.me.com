<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

Route::controller('IOS_yiyuan/goods','modules\IOS_yiyuan\controllers\GoodsController');

Route::controller('IOS_yiyuan/xilie','modules\IOS_yiyuan\controllers\XilieController');
Route::controller('IOS_yiyuan/order','modules\IOS_yiyuan\controllers\OrderController');
Route::controller('IOS_yiyuan/adv','modules\IOS_yiyuan\controllers\AdvController');
Route::controller('IOS_yiyuan/template','modules\IOS_yiyuan\controllers\TemplateController');
Route::controller('IOS_yiyuan/lottery','modules\IOS_yiyuan\controllers\LotteryController');
Route::controller('IOS_yiyuan/address','modules\IOS_yiyuan\controllers\AddressController');
Route::controller('IOS_yiyuan/help','modules\IOS_yiyuan\controllers\HelpController');
Route::controller('IOS_yiyuan/usermessage','modules\IOS_yiyuan\controllers\UsermessageController');
Route::controller('IOS_yiyuan/statistics','modules\IOS_yiyuan\controllers\StatisticsController');