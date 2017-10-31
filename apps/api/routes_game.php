<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

use Youxiduo\V4\Helper\OutUtility;
use Yxd\Services\Cms\GameService;


//添加我的游戏
Route::get('v4/game/add_game',array('before'=>'uri_verify',function(){
	$gid = Input::get('gid');
	$uid = Input::get('uid');
	if(strpos($gid,',')!==false){
		$gids = explode(',',$gid);
	}elseif($gid){
		$gids = $gid;
	}
	$result = Youxiduo\V4\Game\GameService::addUserGame($uid, $gids);
	if($result===true){
		return OutUtility::outSuccess($result);
	}		
	return OutUtility::outError(300,$result);
}));

Route::get('v4/game/is_attent_game',array('before'=>'uri_verify',function(){
	$gid = Input::get('gid');
	$uid = Input::get('uid');
    $result = Youxiduo\V4\Game\GameService::isExistsUserGame($uid, $gid);
	return OutUtility::outSuccess($result);
}));

Route::get('v4/game/remove_game',array('before'=>'uri_verify',function(){
	$gid = Input::get('gid');
	$uid = Input::get('uid');
	if(strpos($gid,',')!==false){
		$gids = explode(',',$gid);
	}elseif($gid){
		$gids = $gid;
	}
	$result = Youxiduo\V4\Game\GameService::removeUserGame($uid, $gids);
	if($result===true){
		return OutUtility::outSuccess($result);
	}		
	return OutUtility::outError(300,$result);
}));

//我的游戏
Route::get('v4/game/user_games',array('before'=>'uri_verify',function(){
	
	$platform = Input::get('platform');
	$uid = Input::get('uid');
	$format = Input::get('format');//info/gid
	$pageIndex = (int)Input::get('pageIndex',1);
	$pageSize = (int)Input::get('pageSize',10);
	if($format=='gid'){
	    $result = Youxiduo\V4\Game\GameService::getUserGameToIds($platform,$uid);
		if(is_array($result)){
			return OutUtility::outSuccess(implode(',',$result));
		}
	}else{
	    $result = Youxiduo\V4\Game\GameService::getUserGameToList($platform,$uid,$pageIndex,$pageSize);
		if(is_array($result)){
			return OutUtility::outSuccess($result['result'],array('totalCount'=>$result['totalCount']));
		}
	}
	return OutUtility::outError(300,$result);
}));

//预约礼包
Route::any('v4/game/reserve_game_giftbag',array('before'=>'uri_verify',function(){
	$platform = Input::get('platform');
	$uid = (int)Input::get('uid');
	$gid = (int)Input::get('gid');
	$result = Youxiduo\V4\Game\GameService::addGameReserve($platform, $uid, $gid);
	if($result===true){
		return OutUtility::outSuccess(array('success'=>true));
	}
	return OutUtility::outError(300,$result);
}));

//我的预约
Route::get('v4/game/user_reserve_games',array('before'=>'uri_verify',function(){
	$platform = Input::get('platform');
	$uid = (int)Input::get('uid');
	$format = Input::get('format');//info/gid
	$pageIndex = (int)Input::get('pageIndex',1);
	$pageSize = (int)Input::get('pageSize',10);
    if($format=='gid'){
	    $result = Youxiduo\V4\Game\GameService::getUserGameReserveToIds($platform,$uid);
		if(is_array($result)){
			return OutUtility::outSuccess(implode(',',$result));
		}
	}else{
	    $result = Youxiduo\V4\Game\GameService::getUserGameReserveToList($platform,$uid,$pageIndex,$pageSize);
		if(is_array($result)){
			return OutUtility::outSuccess($result['result'],array('totalCount'=>$result['totalCount']));
		}
	}
	return OutUtility::outError(300,$result);
}));

//游戏信息
Route::get('v4/game/gameinfo',array('before'=>'uri_verify',function(){
	$gid = Input::get('gid');
	$platform = Input::get('platform');	
	$filter = Input::get('filter','basic');
	if(strpos($gid,',')!==false){
		$gids = explode(',',$gid);
		$result = Youxiduo\V4\Game\GameService::getMultiInfoById($gids, $platform,$filter);
	}else{
	    $result = Youxiduo\V4\Game\GameService::getOneInfoById($gid, $platform,$filter);
	    if(is_array($result)) $result = array($result);
	}
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));
//圈友数
Route::get('v4/game/member_count',array('before'=>'uri_verify',function(){
    $gid = Input::get('gid');
	$platform = Input::get('platform');	
	$filter = Input::get('filter','basic');
	if(strpos($gid,',')!==false){
		$gids = explode(',',$gid);
		$result = Youxiduo\V4\Game\GameService::getMultiGameMemberCount($gids, $platform);
	}else{
	    $result = Youxiduo\V4\Game\GameService::getGameMemberCount((int)$gid, $platform);
	    if(is_array($result)) $result = array($result);
	}
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));

//圈友列表
Route::get('v4/game/member_list',array('before'=>'uri_verify',function(){
    $gid = Input::get('gid');
	$platform = Input::get('platform');	
	$pageIndex = (int)Input::get('pageIndex',1);
	$pageSize = (int)Input::get('pageSize',10);
	$filter = Input::get('filter','basic');
	$result = Youxiduo\V4\Game\GameService::getGameMemberList((int)$gid, $platform,$pageIndex,$pageSize);
	return OutUtility::outSuccess($result);
}));

//搜索游戏
Route::get('v4/game/search_by_name',array('before'=>'uri_verify',function(){
	$game_name = Input::get('gname');
	$platform = Input::get('platform');	
	$filter = Input::get('filter','basic');
	$pageIndex = (int)Input::get('pageIndex',1);
	$pageSize = (int)Input::get('pageSize',10);
        $is_open_forum = (int)Input::get('is_open_forum',0);
	$result = Youxiduo\V4\Game\GameService::searchByName($game_name,$platform,$pageIndex,$pageSize,$is_open_forum,array(),'basic');
	$count = Youxiduo\V4\Game\GameService::searchByNameCount($game_name,$platform,$is_open_forum);
	return OutUtility::outSuccess($result,array('totalCount'=>$count));
}));

/**
 * 热门游戏
 */
Route::get('v4/game/hots',array('before'=>'uri_verify',function(){
    $place = Input::get('place');//home_hot_network/home_hot_single/search_hot
    $size = (int)Input::get('num');
    $platform = Input::get('platform');
    $result = Youxiduo\V4\Game\GameService::getHotGameList($place,1,$size,$platform);
    return OutUtility::outSuccess($result);
}));

Route::get('v4/game/hots_list',array('before'=>'uri_verify',function(){
    $place = Input::get('place');//home_hot_network/home_hot_single/search_hot
    $pageIndex = (int)Input::get('pageIndex',1);
    $pageSize  = (int)Input::get('pageSize',10);
    $platform = Input::get('platform');
	$is_open_forum = (int)Input::get('is_open_forum');
    $result = Youxiduo\V4\Game\GameService::getHotGameList($place,$pageIndex,$pageSize,$platform,$is_open_forum);
    $total = Youxiduo\V4\Game\GameService::getHotGameCount($place,$platform,$is_open_forum);
    return OutUtility::outSuccess($result,array('totalCount'=>$total));
}));

Route::get('v4/game/channels',array('before'=>'uri_verify',function(){
	$result = Youxiduo\V4\Game\GameAreaService::getGameChannelList();
	return OutUtility::outSuccess($result);
}));

//选择游戏区域
Route::get('v4/game/area',array('before'=>'uri_verify',function(){
	$game_id = Input::get('gid');
	$uid = Input::get('uid',0);
	$platform = Input::get('platform');	
	if(strpos($game_id,',')!==false){
	    $game_ids = explode(',',$game_id);
	    $result = array();
	    foreach ($game_ids as $v) {
	        $tmp = array();
	        $tmp['gid'] = $v;
	        $tmp['area'] = Youxiduo\V4\Game\GameService::getGameArea($v,$uid,$platform);
	        $result[] = $tmp;
	    }
	}else{
	    $result = Youxiduo\V4\Game\GameService::getGameArea($game_id,$uid,$platform);
	}
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));

//用户游戏区服
Route::get('v4/game/user_area',array('before'=>'uri_verify',function(){
    $uid = (int)Input::get('uid');
    $gid = Input::get('gid');
    $platform = Input::get('platform');
    $pageIndex = (int)Input::get('pageIndex',1);
	$pageSize = (int)Input::get('pageSize',10);
	$result = Youxiduo\V4\Game\GameService::getUserGameAreaList($uid,$platform,$pageIndex,$pageSize,$gid);
	return OutUtility::outSuccess($result['result'],array('totalCount'=>$result['totalCount']));
}));

//保存用户区服
Route::any('v4/game/save_user_area',array('before'=>'uri_verify',function(){
    $uid = (int)Input::get('uid');
    $platform = Input::get('platform');
    $game_id = (int)Input::get('game_id');
    $rolename = Input::get('game_rolename');
    $area_id = Input::get('area_id');
    $success = Youxiduo\V4\Game\GameService::saveUserGameArea($uid,$game_id,$platform,$rolename,$area_id);
    return OutUtility::outSuccess(array('success'=>$success));
}));

//全量保存用户区服
Route::any('v4/game/save_user_all_area',array('before'=>'uri_verify',function(){
    $uid = (int)Input::get('uid');
    $platform = Input::get('platform');
    $game_id = (int)Input::get('gid');
    $arealist = Input::get('arealist');
    $success = Youxiduo\V4\Game\GameService::saveUserGameAllArea($uid,$game_id,$platform,$arealist);
    return OutUtility::outSuccess(array('success'=>$success));
}));

//推荐玩家
Route::any('v4/game/recommend_users',array('before'=>'uri_verify',function(){
	$uid = (int)Input::get('uid');
    $platform = Input::get('platform');
    $pageIndex = (int)Input::get('pageIndex',1);
	$pageSize = (int)Input::get('pageSize',10);
	$result = Youxiduo\V4\Game\GameService::getRecommendUsers($uid,$platform,$pageIndex,$pageSize);
	return OutUtility::outSuccess($result['result'],array('hasMore'=>$result['hasMore']));
}));

//经典必玩
Route::get('v4/game/mustplay',array('before'=>'uri_verify',function(){
	$platform = Input::get('platform');	
	$pageIndex = (int)Input::get('pageIndex',1);
	$pageSize = (int)Input::get('pageSize',10);
	$result = Youxiduo\V4\Game\GameService::getMustPlay($platform,$pageIndex,$pageSize);
	if(is_array($result)){
		return OutUtility::outSuccess($result['result'],array('totalCount'=>$result['totalCount']));
	}
	return OutUtility::outError(300,$result);
}));

//特色专题
Route::get('v4/game/special_topic_type',array('before'=>'uri_verify',function(){
	$platform = Input::get('platform');
	
	$result = Youxiduo\V4\Game\GameService::getSpecialTopicType($platform);
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
	
}));

//特色专题
Route::get('v4/game/special_topic',array('before'=>'uri_verify',function(){
	$platform = Input::get('platform');	
	$type_id = (int)Input::get('typeId',0);
	$pageIndex = (int)Input::get('pageIndex',1);
	$pageSize = (int)Input::get('pageSize',10);
	$isTop = (int)Input::get('isTop',0);
	$result = Youxiduo\V4\Game\GameService::getSpecialTopic($platform,$type_id,$pageIndex,$pageSize);
	if(is_array($result)){
		return OutUtility::outSuccess($result['result'],array('totalCount'=>$result['totalCount']));
	}
	return OutUtility::outError(300,$result);
}));

//特色专题详情
Route::get('v4/game/special_topic_detail',array('before'=>'uri_verify',function(){
	$platform = Input::get('platform');	
	$topic_id = Input::get('st_id');
	$result = Youxiduo\V4\Game\GameService::getSpecialTopicDetail($platform,$topic_id);
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));

//开测表
Route::get('v4/game/beta_table',array('before'=>'uri_verify',function(){
	$platform = Input::get('platform');	
	$tab = Input::get('tab');
	$pageIndex = (int)Input::get('pageIndex',1);
	$pageSize = (int)Input::get('pageSize',10);
	$result = Youxiduo\V4\Game\GameService::getBetaTable($platform,$tab,$pageIndex,$pageSize);
	if(is_array($result)){
		return OutUtility::outSuccess($result['result'],array('totalCount'=>$result['totalCount']));
	}
	return OutUtility::outError(300,$result);
}));

//最新更新
Route::get('v4/game/lastupdate',array('before'=>'uri_verify',function(){
	
	$platform = Input::get('platform');	
	$pageIndex = (int)Input::get('pageIndex',1);
	$pageSize = (int)Input::get('pageSize',10);
	$result = Youxiduo\V4\Game\GameService::getLastUpdate($platform,$pageIndex,$pageSize);
	if(is_array($result)){
		return OutUtility::outSuccess($result['result'],array('totalCount'=>$result['totalCount']));
	}
	return OutUtility::outError(300,$result);
}));

//游戏类型列表
Route::get('v4/game/typelist',array('before'=>'uri_verify',function(){
	$platform = Input::get('platform');	
	$result = Youxiduo\V4\Game\GameService::getGameTypeToList($platform);
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));

//按类型筛选游戏
Route::get('v4/game/filter_type_game',array('before'=>'uri_verify',function(){
    $platform = Input::get('platform');
    $typeId = Input::get('type_id');
    $pageIndex = (int)Input::get('pageIndex',1);
	$pageSize = (int)Input::get('pageSize',10);
    $is_open_forum = Input::get('is_open_forum',0);
    $result = Youxiduo\V4\Game\GameService::getGameListByTypeId($platform, $typeId,$pageIndex,$pageSize,$is_open_forum);
    $total = Youxiduo\V4\Game\GameService::getGameCountByTypeId($platform, $typeId,$is_open_forum);
    return OutUtility::outSuccess($result,array('totalCount'=>$total));
}));

//全部类型指定数量游戏列表
Route::get('v4/game/all_type_game',array('before'=>'uri_verify',function(){
	$platform = Input::get('platform');	
	$is_open_forum = Input::get('is_open_forum');
	$result = Youxiduo\V4\Game\GameService::getGameTypeToList($platform);
	foreach ($result as &$item) {
	    $item['games'] = Youxiduo\V4\Game\GameService::getGameListByTypeId($platform, $item['id'], 1, 11, $is_open_forum);
	}
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));

//同区玩家
Route::get('v4/game/same_area_user',array('before'=>'uri_verify',function(){
    $uid = (int)Input::get('uid');
    $platform = Input::get('platform');
    $game_id = (int)Input::get('gid');    
    $area_id = Input::get('area_id');
    $pageIndex = (int)Input::get('pageIndex',1);
	$pageSize = (int)Input::get('pageSize',10);
	$result = Youxiduo\V4\Game\GameService::getSameAreaUser($uid,$game_id,$area_id,$pageIndex,$pageSize);
	return OutUtility::outSuccess($result);
}));

//同区玩家
Route::get('v4/game/random_area_user',array('before'=>'uri_verify',function(){
	$uid = (int)Input::get('uid');
    $platform = Input::get('platform');
	$size = (int)Input::get('size',2);
	$result = Youxiduo\V4\Game\GameService::getRandomAreaUser($uid,$size);
	return OutUtility::outSuccess($result);
}));

//添加用户区服
Route::get('v4/game/add_area',array('before'=>'',function(){
    $type = (int)Input::get('type');
	$typename = Input::get('typename');
    $game_id = Input::get('gid');
    $area_name = Input::get('area_name');
	$server_name = Input::get('server_name');
    $uid = Input::get('uid');
    $platform = Input::get('platform');
    $result = Youxiduo\V4\Game\GameService::addGameArea($uid,$type,$typename,$area_name,$game_id,$platform,$server_name);
    if(is_bool($result)){
        return OutUtility::outSuccess(array('success'=>$result));
    }
    return OutUtility::outError(300,$result);
}));

Route::get('v4/game/matching_area',array('before'=>'',function(){
    $uid = Input::get('uid');
	$com_uids = Input::get('com_uids');
	if(strpos($com_uids,',')!==false) {
		$com_uids = explode(',', $com_uids);
	}else{
		$com_uids = array($com_uids);
	}
	$result = Youxiduo\V4\Game\GameService::getMatchingArea($uid,$com_uids);
	return OutUtility::outSuccess($result);
}));

//用户游戏基因
Route::get('v4/game/user_gene',array('before'=>'uri_verify',function(){
    $uid = (int)Input::get('uid');
    $platform = Input::get('platform');
    $pageIndex = (int)Input::get('pageIndex',1);
	$pageSize = (int)Input::get('pageSize',10);
	$result = Youxiduo\V4\Game\GameService::getUserGene($uid,$platform,$pageIndex,$pageSize);
	return OutUtility::outSuccess($result);
}));

//游戏Tag列表
Route::get('v4/game/taglist',array('before'=>'uri_verify',function(){
	$platform = Input::get('platform');	
	$type_id = Input::get('typeId');
	$result = Youxiduo\V4\Game\GameService::getGameTagToList($platform,$type_id);
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));

Route::get('v4/game/query_list',array('before'=>'uri_verify',function() {
	$gid = Input::get('gid');
	$gname = Input::get('gname');
	$typeId = Input::get('typeId');
	$price = Input::get('price');
	$tag = Input::get('tag');
	$is_open_forum = Input::get('is_open_forum');
	$pageIndex = Input::get('pageIndex',1);
	$pageSize = Input::get('pageSize',10);


	$result = Youxiduo\V4\Game\GameService::queryGameList($gid,$gname,$typeId,$price,$tag,$is_open_forum,$pageIndex,$pageSize);

	return OutUtility::outSuccess($result['result'],array('totalCount'=>$result['totalCount']));

}));
//游戏截图列表
Route::get('v4/game/screenshot',array('before'=>'uri_verify',function(){
    $platform = Input::get('platform');
    $game_id = Input::get('gid');
    $result = Youxiduo\V4\Game\GameService::getGameImageList($platform,$game_id);
    return OutUtility::outSuccess($result);
}));

Route::get('club/{ename}',function($ename){
	$result = Youxiduo\V4\Activity\ClubService::getClubOutInfo($ename);
	if($result !== false){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(404,'公会不存在');
});

//获取web游戏信息
Route::get('web/game/info',function(){
    $gid = Input::get('gid');
	$size = (int)Input::get('size',2);
    if($gid){
		$gids = explode(',',$gid);
		$result = Youxiduo\Cms\WebGameService::getGameInfoList($gids);
		return OutUtility::outSuccess($result);
    }else{
		$result = Youxiduo\Cms\WebGameService::getRandGameInfoList($size);
		return OutUtility::outSuccess($result);
	}
    return OutUtility::outError(500,'参数错误');    
});

//礼包列表
Route::get('web/game/giftbag',function(){
    $platform = Input::get('platform');
    $pageSize = Input::get('size',8);
    $result = Youxiduo\Cms\WebGameService::getGiftbagList($platform,$pageSize);
	return OutUtility::outSuccess($result);
});

//礼包列表
Route::get('web/game/game_giftbag',function(){
	$game_id = Input::get('gid');
    $result = Youxiduo\Cms\WebGameService::getGameGiftbagList($game_id);
	return OutUtility::outSuccess($result);
});



//开服列表
Route::get('web/game/beta',function(){
    $pageSize = Input::get('size',9);
    $result = Youxiduo\Cms\WebGameService::getBetaTable($pageSize);
	return OutUtility::outSuccess($result);
});


//侧栏游戏
Route::get('web/game/right_game',function(){
	$pageSize = Input::get('size',6);
	$res = Youxiduo\Cms\WebGameService::getBetaTable($pageSize);
	$result = array();
	if(isset($res['hot'])) $result = $res['hot'];
	return OutUtility::outSuccess($result);
});


//排行榜
Route::get('web/game/rank',function(){
	$type = Input::get('type');
	$result = Youxiduo\Cms\WebGameService::getRankList($type);
	return OutUtility::outSuccess($result);
});

//下载统计
Route::get('v4/game/download',function(){
	$game_id = (int)Input::get('gid');
	$uid = Input::get('uid');
	$result = GameService::download($game_id,$uid);
	$result = array();
	return OutUtility::outSuccess($result);
});

