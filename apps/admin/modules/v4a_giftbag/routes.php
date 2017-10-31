<?php
use Illuminate\Support\Facades\Route;
//礼包
Route::controller('v4agiftbag/gift','modules\v4a_giftbag\controllers\GiftController');
Route::controller('v4agiftbag/report','modules\v4a_giftbag\controllers\ReportController');
Route::controller('v4agiftbag/package','modules\v4a_giftbag\controllers\PackageController');
Route::controller('v4agiftbag/giftcard','modules\v4a_giftbag\controllers\GiftcardController');
Route::controller('v4agiftbag/booking','modules\v4a_giftbag\controllers\BookingController');