<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
//活动
Route::controller('duang/activity','modules\duang\controllers\ActivityController');
//礼包
Route::controller('duang/giftbag','modules\duang\controllers\GiftbagController');
//分享礼包
Route::controller('duang/sharegiftbag','modules\duang\controllers\SharegiftbagController');
//图片管理
Route::controller('duang/pic','modules\duang\controllers\PicController');

//变种分享
Route::controller('duang/variation','modules\duang\controllers\VariationController');
//礼包仓库
Route::controller('duang/gbdepot','modules\duang\controllers\GbdepotController');
//展示列表
Route::controller('duang/show','modules\duang\controllers\ShowController');
