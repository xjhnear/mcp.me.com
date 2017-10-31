<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

Route::controller('plat360/mapping','modules\plat360\controllers\MappingController');
Route::controller('plat360/sync','modules\plat360\controllers\SyncController');

