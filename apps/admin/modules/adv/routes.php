<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

Route::controller('adv/home','modules\adv\controllers\HomeController');
Route::controller('adv/credit','modules\adv\controllers\CreditController');

Route::controller('adv/ads','modules\adv\controllers\AdsController');