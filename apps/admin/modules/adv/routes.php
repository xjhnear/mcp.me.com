<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

//Route::controller('adv/home','modules\adv\controllers\HomeController');
//Route::controller('adv/credit','modules\adv\controllers\CreditController');
Route::controller('adv/apptypes','modules\adv\controllers\ApptypesController');
Route::controller('adv/recommend','modules\adv\controllers\RecommendController');
Route::controller('adv/location','modules\adv\controllers\LocationController');
Route::controller('adv/nature','modules\adv\controllers\NatureController');
Route::controller('adv/releaseadv','modules\adv\controllers\ReleaseadvController');
Route::controller('adv/recommendedadv','modules\adv\controllers\RecommendedadvController');
//Route::controller('adv/ads','modules\adv\controllers\AdsController');
