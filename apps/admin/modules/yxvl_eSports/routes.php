<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

Route::controller('yxvl_eSports/article','modules\yxvl_eSports\controllers\ArticleController');
Route::controller('yxvl_eSports/column','modules\yxvl_eSports\controllers\ColumnController');
Route::controller('yxvl_eSports/live','modules\yxvl_eSports\controllers\LiveController');
Route::controller('yxvl_eSports/video','modules\yxvl_eSports\controllers\VideoController');
Route::controller('yxvl_eSports/sports','modules\yxvl_eSports\controllers\SportsController');
Route::controller('yxvl_eSports/category','modules\yxvl_eSports\controllers\CategoryController');

Route::controller('yxvl_eSports/api','modules\yxvl_eSports\controllers\ApiController');
Route::controller('yxvl_eSports/home','modules\yxvl_eSports\controllers\HomeController');

Route::controller('yxvl_eSports/VltvLive','modules\yxvl_eSports\controllers\VltvLiveController');