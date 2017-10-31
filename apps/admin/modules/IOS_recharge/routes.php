<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;


Route::controller('IOS_recharge/order','modules\IOS_recharge\controllers\OrderController');
Route::controller('IOS_recharge/statistics','modules\IOS_recharge\controllers\StatisticsController');