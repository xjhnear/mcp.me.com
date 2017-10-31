<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
//活动
Route::controller('wxshare/activity','modules\wxshare\controllers\ActivityController');
//礼包
Route::controller('wxshare/giftbag','modules\wxshare\controllers\GiftbagController');
//代充
Route::controller('wxshare/recharge','modules\wxshare\controllers\RechargeController');
//实物
Route::controller('wxshare/goods','modules\wxshare\controllers\GoodsController');
//