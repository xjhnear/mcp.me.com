<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
//Route::controller('home','HomeController');

Route::get('/',function(){
    return Redirect::to('/login');
});

Route::controller('login','LoginController');

Route::get('atme',function(){
	$page = Input::get('pageIndex',1);
	$size = Input::get('pageSize',10);
	$start = Input::get('beginTime',0);
	$end = Input::get('endTime',0);
    $data = Yxd\Services\DataSyncService::syncFeedAtme2($page, $size,$start,$end);
    return Response::json(array('result'=>$data));
});

Route::get('android/push',array('before'=>'uri_verify',function(){
	$apple_token = 'bfd91f4fb65daccf29f7ff75106cdbc652e4d2c145c2fc7a0859dcc66f9d4681';
	Yxd\Modules\Message\PushService::sendOne($apple_token,'测试的推送');
}));





/**
 * 加载模块路由
 */
$modules = Youxiduo\System\Model\Module::getNameList();
foreach($modules as $module=>$name){
	$path = app_path() . '/modules/' . $module . '/routes.php';
	if(file_exists($path)){
		include_once $path;
	}
}


App::missing(function($exception){
	return Response::view('errors.missing', array(), 404);
});
App::error(function($exception){echo '服务器繁忙,请稍候重试';});
