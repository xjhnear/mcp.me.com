<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

//消息推送
Route::controller('a_message/push','modules\a_message\controllers\PushController');
//通知模板
Route::controller('a_message/tpl','modules\a_message\controllers\TplController');
