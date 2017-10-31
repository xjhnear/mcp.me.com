<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

Route::controller('neirong/home','modules\neirong\controllers\HomeController');
Route::controller('neirong/api','modules\neirong\controllers\ApiController');