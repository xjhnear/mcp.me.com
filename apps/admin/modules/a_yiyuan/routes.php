<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

Route::controller('a_yiyuan/goods','modules\a_yiyuan\controllers\GoodsController');

Route::controller('a_yiyuan/xilie','modules\a_yiyuan\controllers\XilieController');
Route::controller('a_yiyuan/order','modules\a_yiyuan\controllers\OrderController');
Route::controller('a_yiyuan/adv','modules\a_yiyuan\controllers\AdvController');
Route::controller('a_yiyuan/template','modules\a_yiyuan\controllers\TemplateController');
Route::controller('a_yiyuan/lottery','modules\a_yiyuan\controllers\LotteryController');
Route::controller('a_yiyuan/address','modules\a_yiyuan\controllers\AddressController');
Route::controller('a_yiyuan/help','modules\a_yiyuan\controllers\HelpController');
Route::controller('a_yiyuan/statistics','modules\a_yiyuan\controllers\StatisticsController');
Route::controller('a_yiyuan/usermessage','modules\a_yiyuan\controllers\UsermessageController');