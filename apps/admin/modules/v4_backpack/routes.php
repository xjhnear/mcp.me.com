<?php
use Illuminate\Support\Facades\Route;
Route::controller('v4backpack/goods','modules\v4_backpack\controllers\GoodsController');
Route::controller('v4backpack/plan','modules\v4_backpack\controllers\PlanController');
Route::controller('v4backpack/record','modules\v4_backpack\controllers\RecordController');
