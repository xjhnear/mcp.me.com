<?php

use Illuminate\Support\Facades\Route;



Route::controller('v4lotteryproduct/lottery','modules\v4_lotteryproduct\controllers\LotteryController');

Route::controller('v4lotteryproduct/bigwheel','modules\v4_lotteryproduct\controllers\BigwheelController');

Route::controller('v4lotteryproduct/lotteryconfig','modules\v4_lotteryproduct\controllers\LotteryconfigController');

Route::controller('v4lotteryproduct/explain','modules\v4_lotteryproduct\controllers\ExplainController');