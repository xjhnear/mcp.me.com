<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

Route::controller('web_forum/forum','modules\web_forum\controllers\ForumController');
Route::controller('web_forum/topic','modules\web_forum\controllers\TopicController');
