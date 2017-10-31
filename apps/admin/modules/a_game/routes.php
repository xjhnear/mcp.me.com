<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

Route::controller('a_game/api','modules\a_game\controllers\ApiController');
Route::controller('a_game/games','modules\a_game\controllers\GamesController');
Route::controller('a_game/pkg','modules\a_game\controllers\PkgController');
Route::controller('a_game/platform','modules\a_game\controllers\PlatformController');