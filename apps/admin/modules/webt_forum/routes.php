<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

Route::controller('webt_forum/forum','modules\webt_forum\controllers\ForumController');
Route::controller('webt_forum/topic','modules\webt_forum\controllers\TopicController');
