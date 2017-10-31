<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

use Youxiduo\V4\Helper\OutUtility;


//文章列表
Route::get('v4/cms/article_list',array('before'=>'uri_verify',function(){
	$platform = Input::get('platform');	
	$channel = Input::get('cate');
	$type_id = (int)Input::get('type_id',0);
	$game_id = (int)Input::get('gid',0);
	$series = (int)Input::get('series',0);
	$sort = Input::get('sort');
	$pageIndex = (int)Input::get('pageIndex',1);
	$pageSize = (int)Input::get('pageSize',10);
	$result = Youxiduo\V4\Cms\ArticleService::getListByCond($platform,$channel,$type_id,$game_id,$series,$sort,$pageIndex,$pageSize);
	if(is_array($result)){
		return OutUtility::outSuccess($result['result'],array('totalCount'=>$result['totalCount']));
	}
	return OutUtility::outSuccess(array());
}));

//文章详情
Route::get('v4/cms/article_detail',array('before'=>'uri_verify',function(){
	$platform = Input::get('platform');	
	$cate = Input::get('cate');
	$id = (int)Input::get('id');
	$result = Youxiduo\V4\Cms\ArticleService::getDetail($platform,$cate,$id);
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));

//视频分类列表
Route::get('v4/cms/video_cate',array('before'=>'uri_verify',function(){
	$platform = Input::get('platform');
	$isTop = (int) Input::get('isTop',0);
	$result = Youxiduo\V4\Cms\ArticleService::getVideoTypeToList($platform, $isTop);
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outSuccess(array());	
}));

//视频列表
Route::get('v4/cms/video_list',array('before'=>'uri_verify',function(){
	$platform = Input::get('platform');	
	$type_id = (int)Input::get('typeId');
	$isTop = (int)Input::get('isTop');
	$sort = Input::get('sort');
	$pageIndex = (int)Input::get('pageIndex',1);
	$pageSize = (int)Input::get('pageSize',10);
	
	$result = Youxiduo\V4\Cms\ArticleService::getVideoListByCond($platform,$type_id,$isTop,$sort,$pageIndex,$pageSize);
	if(is_array($result)){
		return OutUtility::outSuccess($result['result'],array('totalCount'=>$result['totalCount']));
	}
	return OutUtility::outSuccess(array());
	//return OutUtility::outError(300,$result);
}));

//视频列表
Route::get('v4/cms/game_video',array('before'=>'uri_verify',function(){
	$platform = Input::get('platform');	
	$game_id = Input::get('gid');
	$sort = Input::get('sort');
	$pageIndex = (int)Input::get('pageIndex',1);
	$pageSize = (int)Input::get('pageSize',10);
	
	$result = Youxiduo\V4\Cms\ArticleService::getGameVideoList($platform,$game_id,$pageIndex,$pageSize);
	if(is_array($result)){
		return OutUtility::outSuccess($result['result'],array('totalCount'=>$result['totalCount']));
	}
	return OutUtility::outSuccess(array());
}));


//视频详情
Route::get('v4/cms/video_detail',array('before'=>'uri_verify',function(){
	$platform = Input::get('platform');	
	$vid = (int)Input::get('vid');
	$video_type = Input::get('video_type');
	if($video_type=='article'){
	    $result = Youxiduo\V4\Cms\ArticleService::getVideoDetail($platform,$vid);
	}else{
		$result = Youxiduo\V4\Cms\ArticleService::getGameVideoDetail($platform,$vid);
	}
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outSuccess((object)array());
}));

//
Route::get('v4/cms/search',array('before'=>'uri_verify',function(){
	$platform = Input::get('platform');	
	
}));


Route::get('v4/cms/article_number',array('before'=>'uri_verify',function(){
	$channel = Input::get('cate');
	$time = strtotime(Input::get('query_time',date('Y-m-d H:i:s')));
	$result = Youxiduo\V4\Cms\ArticleService::getArticleNumber($channel,$time);
	return OutUtility::outSuccess($result);
}));

Route::get('v4/cms/game_article_number',array('before'=>'uri_verify',function(){
	$platform = Input::get('platform');
	$gid = Input::get('gid');
	if(strpos($gid,',')!==false){
		$gids = explode(',',$gid);
	}elseif($gid){
		$gids = array($gid);
	}

	$result = Youxiduo\V4\Cms\ArticleService::getGameArticleNumber($gids);

	return OutUtility::outSuccess($result);

}));
