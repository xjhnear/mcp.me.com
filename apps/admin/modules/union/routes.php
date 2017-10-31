<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
//活动
//充值返现活动管理
Route::controller('union/cash','modules\union\controllers\CashgameController');
//综合banner
Route::controller('union/banner','modules\union\controllers\BannerController');
