<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

Route::controller('statistics/rank','modules\statistics\controllers\RankController');
Route::controller('statistics/usercredit','modules\statistics\controllers\UsercreditController');
Route::controller('statistics/forum','modules\statistics\controllers\ForumController');
Route::controller('statistics/tuiguang','modules\statistics\controllers\TuiguangController');
Route::controller('statistics/adv','modules\statistics\controllers\AdvController');
Route::controller('statistics/monitor','modules\statistics\controllers\MonitorController');