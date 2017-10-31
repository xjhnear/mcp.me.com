<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

Route::controller('activity/hunt','modules\activity\controllers\HuntController');
Route::controller('activity/rule','modules\activity\controllers\RuleController');
Route::controller('activity/gameask','modules\activity\controllers\GameAskController');
Route::controller('activity/prize','modules\activity\controllers\PrizeController');
Route::controller('activity/event','modules\activity\controllers\EventController');
Route::controller('activity/report','modules\activity\controllers\ReportController');
Route::controller('activity/club','modules\activity\controllers\ClubController');