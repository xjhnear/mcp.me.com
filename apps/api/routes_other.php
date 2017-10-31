<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

use Youxiduo\V4\Helper\OutUtility;

//应用配置
Route::get('v4/app/config',array('before'=>'uri_verify',function(){
	$platform = Input::get('platform');	
	$appname = Input::get('appname','');
	$channel = Input::get('channel','');
	$version = Input::get('version');
	$result = Youxiduo\V4\App\ConfigService::getVersionConfig($platform,$appname,$channel,$version);
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));

Route::get('v4/app/check-version',array('before'=>'uri_verify',function(){
	$platform = Input::get('platform');	
	$appname = Input::get('appname','');
	$channel = Input::get('channel','');
	$version = Input::get('version');
	$result = Youxiduo\V4\App\ConfigService::getcheckVersion($platform,$appname,$channel,$version);
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));

Route::get('v4/select_area',array('before'=>'uri_verify',function(){
	//$platform = Input::get('platform');	
	$type = Input::get('type');
	$id = Input::get('area_id');
	$result = Youxiduo\V4\User\UserService::getArea($id, $type);
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));
//赞/围观
Route::get('v4/do_like',array('before'=>'uri_verify',function(){
	$uid = Input::get('uid');
	$target_id = Input::get('target_id');
	$target_table = Input::get('target_table');
	$result = Youxiduo\V4\User\LikeService::doLike($uid, $target_id, $target_table);
	if(is_bool($result)){
		return OutUtility::outSuccess(array('success'=>$result));
	}
	return OutUtility::outError(300,$result);
}));

//赞/围观
Route::get('v4/is_like',array('before'=>'uri_verify',function(){
	$uid = Input::get('uid');
	$target_id = Input::get('target_id');
	$target_table = Input::get('target_table');
	$result = Youxiduo\V4\User\LikeService::isLike($uid, $target_id, $target_table);
	if(is_bool($result)){
		return OutUtility::outSuccess(array('success'=>$result));
	}
	return OutUtility::outError(300,$result);
}));

//获取假人UID 评论用
Route::get('v4/get_robot_uid',array('before'=>'uri_verify',function(){
    $robot_list = array('6537796','6537797','6537800','6537802','6537804','6537807','6537808','6537810','6537811','6537812','6537813','6537814','6537815','6537816','6537817','6537818','6537820','6537821','6537822','6537823','6537824','6537825','6537826','6537827','6537828','6537829','6537830','6537831','6537832','6537833','6537834','6537835','6537836','6537837','6537838','6537839','6537840','6537841','6537842','6537843','6537844','6537845','6537847','6537848','6537849','6537850','6537851','6537852','6537853','6537854','6537855','6537856','6537857','6537858','6537859','6537860','6537861','6537862','6537863','6537864','6537865','6537866','6537867','6537868','6537869','6537870','6537871','6537872','6537873','6537874','6537875','6537876','6537877','6537878','6537879','6537880','6537881','6537882','6537883','6537884','6537885','6537886','6537887','6537888','6537889','6537890','6537891','6537892','6537893','6537894','6537895','6537896','6537897','6537898','6537899','6537900','6537901','6537902','6537903','6537904');
	$k = rand(0, 99);
	if(isset($robot_list[$k])){
		return OutUtility::outSuccess($robot_list[$k]);
	}
	return OutUtility::outError(300,$result);
}));


//赞/围观总数
Route::get('v4/like_count',array('before'=>'uri_verify',function(){
	$target_id = Input::get('target_id');
	$target_table = Input::get('target_table');
	$result = Youxiduo\V4\User\LikeService::getLikeCount($target_id, $target_table);
	if(is_numeric($result)){
		return OutUtility::outSuccess(array('totalCount'=>$result));
	}
	//return OutUtility::outError(300,$result);
	return OutUtility::outSuccess(array('totalCount'=>0));
}));

//赞/围观列表
Route::get('v4/like_list',array('before'=>'uri_verify',function(){
	$target_id = Input::get('target_id');
	$target_table = Input::get('target_table');
	$pageIndex = (int)Input::get('pageIndex',1);
	$pageSize = (int)Input::get('pageSize',10);
	$uid = Input::get('uid');
	$result = Youxiduo\V4\User\LikeService::getLikeList($target_id, $target_table,$pageIndex,$pageSize,$uid);
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	//return OutUtility::outError(300,$result);
	return OutUtility::outSuccess(array());
}));


//赞/围观总数
Route::get('v4/like_counts',array('before'=>'uri_verify',function(){
	$target_ids_str = Input::get('target_ids');
	$target_table = Input::get('target_table');
	if(strpos($target_ids_str,',')!==false){
		$target_ids = explode(',',$target_ids_str);
	}else{
		$target_ids = array($target_ids_str);
	}
	$result = Youxiduo\V4\User\LikeService::getLikeCountByTids($target_ids, $target_table);
	$out = array();
	foreach($target_ids as $id){
		$out[$id] = isset($result[$id]) ? $result[$id] : 0;
	}
	if(is_array($out)){
		return OutUtility::outSuccess($out);
	}
	//return OutUtility::outError(300,$result);
	return OutUtility::outSuccess(array());
}));

//赞/围观列表
Route::get('v4/like_lists',array('before'=>'uri_verify',function(){
	$target_ids_str = Input::get('target_ids');
	$target_table = Input::get('target_table');
	$uid = Input::get('uid');
	$size = (int)Input::get('size',2);
    if(strpos($target_ids_str,',')!==false){
		$target_ids = explode(',',$target_ids_str);
	}else{
		$target_ids = array($target_ids_str);
	}
	//$result = Youxiduo\V4\User\LikeService::getLikeListByTids($target_ids, $target_table);
    $out = array();
	foreach($target_ids as $id){		
		//$out[$id] = isset($result[$id]) ? $result[$id] : array();
		$result = Youxiduo\V4\User\LikeService::getLikeList($id, $target_table,1,$size,$uid); 
		$out[$id] = $result;
	}
	if(is_array($out)){
		return OutUtility::outSuccess($out);
	}
	//return OutUtility::outError(300,$result);
	return OutUtility::outSuccess(array());
}));



//小秘书认证信息
Route::get('v4/sproject/info',array('before'=>'uri_verify',function(){
	$data = array();
	$config = Yxd\Modules\System\SettingService::getConfig('sproject_attestation');
	if($config){
		$data['result'] = $config['data'];
	}
	empty($data['result']['icon']) ? : $data['result']['icon'] = Yxd\Services\Service::joinImgUrl($data['result']['icon']);
	if(!empty($data)){
		return OutUtility::outSuccess(array('success'=>$data));
	}
	return OutUtility::outError(300,$data);
}));