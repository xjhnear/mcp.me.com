<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;


//操作记录
Route::controller('phone/batch','modules\phone\controllers\BatchController');
Route::controller('phone/category','modules\phone\controllers\CategoryController');

