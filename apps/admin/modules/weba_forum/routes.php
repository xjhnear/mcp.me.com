<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

Route::controller('weba_forum/topic','modules\weba_forum\controllers\TopicController');
Route::controller('weba_forum/bbs','modules\weba_forum\controllers\BbsController');
Route::controller('weba_forum/replylimit','modules\weba_forum\controllers\ReplylimitController');
