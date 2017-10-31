<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

//礼包
Route::controller('a_giftbag/gift','modules\a_giftbag\controllers\GiftController');
Route::controller('a_giftbag/giftcard','modules\a_giftbag\controllers\GiftCardController');
