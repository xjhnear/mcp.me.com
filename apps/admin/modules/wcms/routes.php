<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

Route::controller('wcms/article','modules\wcms\controllers\ArticleController');
Route::controller('wcms/picture','modules\wcms\controllers\PictureController');
Route::controller('wcms/video','modules\wcms\controllers\VideoController');
Route::controller('wcms/category','modules\wcms\controllers\CategoryController');

Route::controller('wcms/api','modules\wcms\controllers\ApiController');
Route::controller('wcms/home','modules\wcms\controllers\HomeController');