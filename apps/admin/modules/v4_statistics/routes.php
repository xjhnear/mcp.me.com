<?php

use Illuminate\Support\Facades\Route;


Route::controller('v4statistics/account','modules\v4_statistics\controllers\AccountController');
Route::controller('v4statistics/task','modules\v4_statistics\controllers\TaskController');
Route::controller('v4statistics/CMS','modules\v4_statistics\controllers\CMSController');
Route::controller('v4statistics/appaccount','modules\v4_statistics\controllers\AppAccountController');
