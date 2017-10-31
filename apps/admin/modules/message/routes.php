<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

//消息推送
Route::controller('message/push','modules\message\controllers\PushController');
//通知模板
Route::controller('message/tpl','modules\message\controllers\TplController');

//Route::controller('message/push','modules\message\controllers\PushController');