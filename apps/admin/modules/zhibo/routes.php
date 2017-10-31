<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/5/14
 * Time: 14:07
 */
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;

Route::controller('zhibo/guest','modules\zhibo\controllers\GuestController');
Route::controller('zhibo/game','modules\zhibo\controllers\GameController');
Route::controller('zhibo/plat','modules\zhibo\controllers\PlatController');
