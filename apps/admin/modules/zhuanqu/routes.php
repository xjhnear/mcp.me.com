<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

Route::controller('zhuanqu/home','modules\zhuanqu\controllers\HomeController');
Route::controller('zhuanqu/help','modules\zhuanqu\controllers\HelpController');