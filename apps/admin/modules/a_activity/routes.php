<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

//活动
Route::controller('a_activity/activity','modules\a_activity\controllers\ActivityController');

Route::controller('a_activity/atask','modules\a_activity\controllers\AtaskController');
