<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
//活动
Route::controller('duang/activity','modules\duang\controllers\ActivityController');
//礼包
Route::controller('duang/giftbag','modules\duang\controllers\GiftbagController');
//分享礼包
Route::controller('duang/sharegiftbag','modules\duang\controllers\SharegiftbagController');
//图片管理
Route::controller('duang/pic','modules\duang\controllers\PicController');
