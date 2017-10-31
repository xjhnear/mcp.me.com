<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

Route::controller('common/home','modules\common\controllers\HomeController');
Route::controller('common/uploader','modules\common\controllers\UploaderController');