<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

//礼包
Route::controller('v4giftbag/gift','modules\v4_giftbag\controllers\GiftController');
Route::controller('v4giftbag/package','modules\v4_giftbag\controllers\PackageController');
Route::controller('v4giftbag/giftactivity','modules\v4_giftbag\controllers\GiftActivityController');
