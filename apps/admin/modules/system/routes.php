<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

//设置
Route::controller('system/setting','modules\system\controllers\SettingController');
//积分
Route::controller('system/credit','modules\system\controllers\CreditController');
//等级
Route::controller('system/grade','modules\system\controllers\GradeController');
//任务
Route::controller('system/task','modules\system\controllers\TaskController');
//通知
Route::controller('system/notice','modules\system\controllers\NoticeController');
//首页图片设置
Route::controller('system/picture','modules\system\controllers\PictureSettingController');
//API性能统计
Route::controller('system/profile','modules\system\controllers\ProfileController');

Route::controller('system/share','modules\system\controllers\ShareController');

//Route::controller('system/privilege','modules\system\controllers\PrivilegeController');
