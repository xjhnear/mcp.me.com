<?php

//use Illuminate\Support\Facades\Route;

Route::controller('gamelive/article','modules\gamelive\controllers\ArticleController');
Route::controller('gamelive/anchor','modules\gamelive\controllers\AnchorController');
Route::controller('gamelive/category','modules\gamelive\controllers\CategoryController');
Route::controller('gamelive/column','modules\gamelive\controllers\ColumnController');
Route::controller('gamelive/video','modules\gamelive\controllers\VideoController');
Route::controller('gamelive/game','modules\gamelive\controllers\GameController');
Route::controller('gamelive/api','modules\gamelive\controllers\ApiController');
Route::controller('gamelive/home','modules\gamelive\controllers\HomeController');