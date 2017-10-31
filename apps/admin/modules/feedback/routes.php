<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

//用户反馈
Route::controller('feedback/chat','modules\feedback\controllers\ChatController');
Route::controller('feedback/achat','modules\feedback\controllers\AchatController');
