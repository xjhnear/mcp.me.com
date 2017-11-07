<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
//Route::controller('home','HomeController');

Route::get('/',function(){
    return Redirect::to('/common/home/index');
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
