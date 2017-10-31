<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

//礼包
Route::controller('giftbag/gift','modules\giftbag\controllers\GiftController');
Route::controller('giftbag/giftcard','modules\giftbag\controllers\GiftCardController');
