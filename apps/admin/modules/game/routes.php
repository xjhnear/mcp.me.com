<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

Route::controller('game/api','modules\game\controllers\ApiController');
Route::controller('game/games','modules\game\controllers\GamesController');
Route::controller('game/data','modules\game\controllers\DataController');
Route::controller('game/area','modules\game\controllers\AreaController');
Route::controller('game/premiere','modules\game\controllers\PremiereController');
//Route::controller('game/gametype','modules\game\controllers\GametypeController');
Route::controller('game/gametype','modules\game\controllers\GametypeController');
Route::controller('game/mustplay','modules\game\controllers\MustplayController');
 