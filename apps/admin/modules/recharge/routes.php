<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;


Route::controller('recharge/order','modules\recharge\controllers\OrderController');
Route::controller('recharge/statistics','modules\recharge\controllers\StatisticsController');