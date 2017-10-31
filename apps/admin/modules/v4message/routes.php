<?php

//消息推送
Route::controller('v4message/push','modules\v4message\controllers\PushController');

Route::controller('v4message/tpl','modules\v4message\controllers\TplController');
Route::controller('v4message/tpl2','modules\v4message\controllers\Tpl2Controller');
Route::controller('v4message/filter','modules\v4message\controllers\FilterController');