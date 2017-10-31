<?php
/**
 * @package Youxiduo
 * @category Base 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\V4\Game;

use Youxiduo\V4\User\UserService;

use Youxiduo\V4\Game\Model\IosGameSchemes;

use Youxiduo\V4\Game\Model\GameCollectType;

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

use Youxiduo\V4\Game\Model\Game;
use Youxiduo\V4\Game\Model\IosGame;
use Youxiduo\V4\Game\Model\AndroidGame;
use Youxiduo\V4\Game\Model\GameMustPlay;
use Youxiduo\V4\Game\Model\GameCollect;
use Youxiduo\V4\Game\Model\GameCollectGames;
use Youxiduo\V4\Game\Model\GameType;
use Youxiduo\V4\Game\Model\Tag;
use Youxiduo\V4\Game\Model\GameBeta;
use Youxiduo\V4\Game\Model\GameTag;
use Youxiduo\V4\Game\Model\GameArea;
use Youxiduo\V4\Game\Model\UserGameArea;
use Youxiduo\V4\Game\Model\GameImage;

use Youxiduo\V4\Game\Model\UserGame;
use Youxiduo\V4\Game\Model\UserGameReserve;

class GameService extends BaseService
{
	public static $languages = array('0'=>'未知','1'=>'中文','2'=>'英文','3'=>'其他');
	
	const BETA_TABLE_TAB_TODAY = 'today';
	const BETA_TABLE_TAB_SOON = 'soon';
	const BETA_TABLE_TAB_OVER = 'over';
	
	const ERROR_PARAMS_MISS = 'params_miss';
	const ERROR_GAME_NOT_EXISTS = 'game_not_exists';//游戏不存在
	const ERROR_PLATFORM_NOT_EXISTS = 'platform_not_exists';//平台不存在
	const ERROR_SPECIAL_TOPIC_NOT_EXISTS = 'special_topic_not_exists';//特色专题不存在
	const ERROR_GAME_RESERVE_ERROR = 'game_reserve_error';//游戏预约失败
	const ERROR_GAME_AREA_NOT_EXISTS = 'game_area_not_exists';//游戏区服不存在
	
	/**
	 * 获取单个游戏信息
	 * @param int $gid 游戏ID
	 * @param string $platform 平台
	 * @param string $filter 过滤器
	 * 
	 * @return array|string 存在返回游戏信息数组
	 */
	public static function getOneInfoById($gid,$platform,$filter='basic')
	{
		if($platform=='ios'){
			$game = IosGame::getInfoById($gid);
			$schemes = IosGameSchemes::getSchemesToKeyValue($gid);
		}elseif($platform=='android'){
			$game = AndroidGame::getInfoById($gid);
		}
		if(!$game) return self::ERROR_GAME_NOT_EXISTS;
		$gametype = GameType::getListToKeyValue();
		$game = self::filterField($game,$filter,$gametype);		
		$platform=='ios' && $game['schemes'] = isset($schemes[$game['gid']]) ? $schemes[$game['gid']] : '';		
		return $game;
	}

    /**
     * 获取多个游戏信息
     * @param $gids
     * @param string $platform 平台
     * @param string $filter 过滤器
     * @return array|string 存在返回游戏信息数组
     * @internal param int $gid 游戏ID
     */
    public static function getMultiInfoById($gids,$platform,$filter='basic')
	{	
		if($platform=='ios'){
			$games = IosGame::getMultiInfoById($gids);
			$schemes = IosGameSchemes::getSchemesToKeyValue($gids);
		}elseif($platform=='android'){
			$games = AndroidGame::getMultiInfoById($gids);
		}
		if(!$games) return self::ERROR_GAME_NOT_EXISTS;
		$gametype = GameType::getListToKeyValue();
		foreach($games as $key=>$game){
		    $game = self::filterField($game,$filter,$gametype);
		    $platform=='ios' && $game['schemes'] = isset($schemes[$game['gid']]) ? $schemes[$game['gid']] : '';
		    $games[$key] = $game;
		}
		return $games;
	}
	
	public static function getGameImageList($platform,$game_id)
	{
		$images = array();
		if($platform=='ios'){
			$images = GameImage::db()->where('gid','=',$game_id)->lists('litpic');
		}elseif($platform=='android'){
			
		}
		foreach($images as $key=>$image){
			$images[$key] = array('image'=>Utility::getImageUrl($image));
		}
		return $images;
	}
	
	public static function searchByName($gname,$platform,$pageIndex=1,$pageSize=10,$order=array(),$filter='basic')
	{
		$games = array();
		if($platform == 'ios'){
			$games = IosGame::db()->where('shortgname','like','%'.$gname.'%')->where('isdel','=',0)->forPage($pageIndex,$pageSize)->get();
		}elseif($platform == 'android'){
			$games = AndroidGame::db()->where('shortgname','like','%'.$gname.'%')->where('isdel','=',0)->forPage($pageIndex,$pageSize)->get();
		}
		$gametype = GameType::getListToKeyValue();
		foreach($games as $key=>$game){
			$games[$key] = self::filterField($game,$filter,$gametype);
		}
		return $games;
	}
	
	public static function searchByNameCount($gname,$platform)
	{
	    if($platform == 'ios'){
			return IosGame::db()->where('shortgname','like','%'.$gname.'%')->where('isdel','=',0)->count();
		}elseif($platform == 'android'){
			return AndroidGame::db()->where('shortgname','like','%'.$gname.'%')->where('isdel','=',0)->count();
		}
		return 0;
	}
	
	/**
	 * 通过类型获取游戏信息
	 */
	public static function getGameListByTypeId($platform,$typeId,$pageIndex=1,$pageSize=10)
	{
		$games = array();
	    if($platform == 'ios'){
			$games = IosGame::db()->where('type','=',$typeId)->where('isdel','=',0)->orderBy('id','desc')->forPage($pageIndex,$pageSize)->get();
		}elseif($platform == 'android'){
			$games = AndroidGame::db()->where('type','=',$typeId)->where('isdel','=',0)->orderBy('id','desc')->forPage($pageIndex,$pageSize)->get();
		}
		$gametype = GameType::getListToKeyValue();
		foreach($games as $key=>$game){
			$games[$key] = self::filterField($game,'basic',$gametype);
		}
		return $games;
	}
	
    public static function getGameCountByTypeId($platform,$typeId)
	{		
	    if($platform == 'ios'){
			return IosGame::db()->where('type','=',$typeId)->where('isdel','=',0)->count();
		}elseif($platform == 'android'){
			return AndroidGame::db()->where('type','=',$typeId)->where('isdel','=',0)->count();
		}
	}
	
	/**
	 * 热门游戏
	 * @param string $place 位置参数 home_hot_network/home_hot_single
	 * @param int $size 数量限制
	 */
	public static function getHotGameList($place,$pageIndex,$pageSize,$platform)
	{
		$games = array();
		if(self::checkPlatform($platform)!==true) return $games;
		if($platform=='ios'){
		    $games = self::getIosHotGameList($place,$pageIndex,$pageSize);
		}elseif($platform == 'android'){
			
		}
		if(!$games) return $games;
		$gametype = GameType::getListToKeyValue();
		foreach($games as $key=>$game){
			$games[$key] = self::filterField($game,'basic',$gametype);			
		}
		return $games;
	}
	
	public static function getHotGameCount($place,$platform)
	{
		if(self::checkPlatform($platform)!==true) return 0;
		if($platform=='ios'){
		    return self::getIosHotGameCount($place);
		}elseif($platform == 'android'){
			return 0;
		}
		return 0;
	}
	
	protected static function getIosHotGameList($place,$pageIndex,$pageSize)
	{
		$tb = IosGame::db()->where('isdel','=',0);
		switch($place){
			case 'home_hot_network':
				$tb = $tb->where('type','=',11);//->orderBy('sort','desc');
				break;
			case 'home_hot_single':
				$tb = $tb->where('type','<>',11);//->orderBy('sort','desc');
				break;
			case 'search_hot':
				$tb = $tb->orderBy('downtimes','desc');
				break;
		}
		$games = $tb->orderBy('id','desc')->forPage($pageIndex,$pageSize)->get();
		return $games;
	}
	
    protected static function getIosHotGameCount($place)
	{
		$tb = IosGame::db()->where('isdel','=',0);
		switch($place){
			case 'home_hot_network':
				$tb = $tb->where('type','=',11)->orderBy('sort','desc');
				break;
			case 'home_hot_single':
				$tb = $tb->where('type','<>',11)->orderBy('sort','desc');
				break;
			case 'search_hot':
				$tb = $tb->orderBy('downtimes','desc');
				break;
		}
		return $tb->count();
	}
	
	/**
	 * 游戏测试表
	 * @param string $platform
	 * @param string $tab 开测类型 today:今日开测,soon:即将开测,over:已经开测
	 * @param int $pageIndex
	 * @param int $pageSize
	 */
	public static function getBetaTable($platform,$tab,$pageIndex=1,$pageSize=10)
	{
		if(self::checkPlatform($platform)!==true) return self::ERROR_PLATFORM_NOT_EXISTS;
		$search = array('platform'=>$platform);
		$start = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$end = mktime(23,59,59,date('m'),date('d'),date('Y'));
		$beta_games = array();
		$total = 0;
		if($tab == self::BETA_TABLE_TAB_TODAY){//今日开测
						
			$hot_search['condition'] = array(array('field'=>'istop','logic'=>'=','value'=>1));
			$hot = GameBeta::search(array_merge($search,$hot_search),1,100,array('addtime'=>'desc','id'=>'desc'));
			$hot_beta_games = $hot['result'];
			
			$today_search['condition'] = array(
			    array('field'=>'istop','logic'=>'=','value'=>0),
			    array('field'=>'addtime','logic'=>'=','value'=>$start)
			);
			$today = GameBeta::search(array_merge($search,$today_search),1,100,array('addtime'=>'desc','id'=>'desc'));
			$today_beta_games = $today['result'];
			
			$tomorrow_search['condition'] = array(
			    array('field'=>'istop','logic'=>'=','value'=>0),
			    array('field'=>'addtime','logic'=>'=','value'=>$start+3600*24)
			);
			$tomorrow = GameBeta::search(array_merge($search,$tomorrow_search),1,100,array('addtime'=>'desc','id'=>'desc'));
			$tomorrow_beta_games = $tomorrow['result'];
			
			$future_week_search['condition'] = array(
			    array('field'=>'istop','logic'=>'=','value'=>0),
			    array('field'=>'addtime','logic'=>'>','value'=>$start+3600*24),
			    array('field'=>'addtime','logic'=>'<','value'=>$start+3600*24*7),
			);
			$future = GameBeta::search(array_merge($search,$future_week_search),1,100,array('addtime'=>'asc','id'=>'desc'));
			$future_week_beta_games = $future['result'];
			
			$bygone_week_search['condition'] = array(
			    array('field'=>'istop','logic'=>'=','value'=>0),
			    array('field'=>'addtime','logic'=>'<','value'=>$start-3600*24),
			    array('field'=>'addtime','logic'=>'>','value'=>$start-3600*24*8),
			);
			$bygone = GameBeta::search(array_merge($search,$bygone_week_search),1,100,array('addtime'=>'desc','id'=>'desc'));
			$bygone_week_beta_games = $bygone['result'];

			$all_beta_games = array_merge($hot_beta_games,$today_beta_games,$tomorrow_beta_games,$future_week_beta_games,$bygone_week_beta_games);
			
			$pages = array_chunk($all_beta_games,$pageSize,false);
			$beta_games = isset($pages[$pageIndex-1]) ? $pages[$pageIndex-1] : array();
			$total = count($all_beta_games);
			
		}elseif($tab == self::BETA_TABLE_TAB_SOON){//即将开测
			
			$order = array('addtime'=>'asc','id'=>'desc');
			$search['condition'] = array(array('field'=>'addtime','logic'=>'>','value'=>$end));
			$result = GameBeta::search($search,$pageIndex,$pageSize,$order);
			$beta_games = $result['result'];
			$total = $result['totalCount'];
			
		}elseif($tab == self::BETA_TABLE_TAB_OVER){//已经开测
			
			$order = array('addtime'=>'desc','id'=>'desc');
			$search['condition'] = array(array('field'=>'addtime','logic'=>'<','value'=>$start));
			$result = GameBeta::search($search,$pageIndex,$pageSize,$order);
			$beta_games = $result['result'];
			$total = $result['totalCount'];
		}
		
		//if(!$beta_games) return self::ERROR_GAME_NOT_EXISTS;
		if(!$beta_games) return array('result'=>array(),'totalCount'=>0);
		
		$gids = array();		
		foreach($beta_games as $row){
			$gids[] = $platform=='ios' ? $row['gid'] : $row['agid'];
		}
		$games = $platform=='ios' ? IosGame::getMultiInfoById($gids,true) : AndroidGame::getMultiInfoById($gids,true);
		$gametype = GameType::getListToKeyValue();		
		$tags = GameTag::getGameTagsByGameIds($platform, $gids);
		$out = array();
		foreach($beta_games as $row){
			if($platform=='ios'){
				if(!isset($games[$row['gid']])) continue;
			}else{
				if(!isset($games[$row['agid']])) continue;
			}
			$game = $platform == 'ios' ? $games[$row['gid']] : $games[$row['agid']];
			$tmp = array();
			$tmp['gid'] = $game['id'];
			$tmp['title'] = $row['title'];
			$tmp['istop'] = $platform == 'ios' ? ($row['istop'] ? true : false) : $row['istop'];
			$tmp['state'] = $row['state'];
			$tmp['adddate'] = date('Y-m-d',$row['addtime']);
			if($platform == 'ios'){
			    $tmp['typename'] = isset($gametype[$game['type']]) ? $gametype[$game['type']] : '';
			}else{
				$tmp['gametype'] = isset($gametype[$game['type']]) ? $gametype[$game['type']] : '';
			}
			$tmp['isfirst'] = $platform == 'ios' ? ($game['isstarting'] ? true : false) : $game['isstarting'];
			$tmp['pic'] = Utility::getImageUrl($game['ico']);
			$tmp['openbeta'] = $row['openbeta'];
			if($platform == 'ios'){
			    $tmp['tips'] = isset($tags[$game['id']]) ? implode(',',$tags[$game['id']]) : '';
			}else{
				$tmp['tag'] = isset($tags[$game['id']]) ? implode(',',$tags[$game['id']]) : '';
			}
			$out[] = $tmp;
			
		}
		return array('result'=>$out,'totalCount'=>$total);
	}
	
	/**
	 * 经典必玩
	 * 
	 * @param string $platform
	 * @param int $pageIndex
	 * @param int $pageSize
	 * 
	 */
	public static function getMustPlay($platform,$pageIndex=1,$pageSize=10)
	{
		if(self::checkPlatform($platform)!==true) return self::ERROR_PLATFORM_NOT_EXISTS;
		$result = GameMustPlay::getList($platform,$pageIndex,$pageSize);		
		$total = GameMustPlay::getCount($platform);
		$out = array();
		foreach($result as $row){
			$data = array();
			$data['gid'] = $platform=='ios' ? $row['gid'] : $row['agid'];
			$data['title'] = $row['title'];
			$data['img'] = Utility::getImageUrl($row['pic']);
			$out[] = $data;
		}
		return array('result'=>$out,'totalCount'=>$total);
	}
	
	/**
	 * 特色专题类型
	 */
	public static function getSpecialTopicType($platform)
	{
		if(self::checkPlatform($platform)!==true) return self::ERROR_PLATFORM_NOT_EXISTS;
		$result = GameCollectType::getList($platform);
		$out = array();
		foreach($result as $row){
			$tmp['type_id'] = $row['type_id'];
			$tmp['type_name'] = $row['type_name'];
			
			$out[] = $tmp;
		}
		return $out;
	}
	
	/**
	 * 特色专题
	 * 
	 * @param string $platform
	 * @param int $pageIndex
	 * @param int $pageSize
	 * 
	 */
	public static function getSpecialTopic($platform,$type_id,$pageIndex=1,$pageSize=10)
	{
		if(self::checkPlatform($platform)!==true) return self::ERROR_PLATFORM_NOT_EXISTS;
		$result = GameCollect::getList($platform,$type_id,$pageIndex,$pageSize);
		$total = GameCollect::getCount($platform,$type_id);
		
		$zt_ids = array();//专题IDs
		foreach($result as $row){
			$zt_ids[] = $row['id'];
		}
		
		$zt_games = GameCollectGames::getListByIds($platform,$zt_ids);
		$zt_games_gids = array();//游戏IDs
		foreach($zt_games as $row){
			$zt_games_gids[$row['zt_id']][] = $platform=='ios' ? $row['gid'] : $row['agid'];
		}
		$gids = array();
		foreach($zt_games_gids as $_gids){
			$gids = array_merge($gids,$_gids);
		}
		if($platform=='ios'){
			$games = IosGame::getMultiInfoById($gids,true);
		}else{
			$games = AndroidGame::getMultiInfoById($gids,true);
		}
		$out = array();
		foreach($result as $row){
			$data = array();
			$data['tid'] = $row['id'];
			$data['title'] = $row['ztitle'];
			$data['img'] = Utility::getImageUrl($row['litpic']);
			$data['updatetime'] = date('Y-m-d',$row['addtime']);
			$data['viewcount'] = $row['viewtimes'];
			if(isset($zt_games_gids[$row['id']])){
				foreach($zt_games_gids[$row['id']] as $gid){
					if(!$gid || !isset($games[$gid])) continue;
					$game = $games[$gid];
					$tmp['gid'] = $game['id'];
					$tmp['img'] = Utility::getImageUrl($game['ico']);
					$data['games'][] = $tmp;
				}
				$data['gamecount'] = count($zt_games_gids[$row['id']]);
			}else{
				$data['games'] = array();
				$data['gamecount'] = 0;
			}
			$out[] = $data;
		}
		
		return array('result'=>$out,'totalCount'=>$total);
	}
	
    /**
	 * 特色专题详情
	 * 
	 * @param string $platform
	 * @param int $zt_id
	 * 
	 */
	public static function getSpecialTopicDetail($platform,$zt_id)
	{
		if(self::checkPlatform($platform)!==true) return self::ERROR_PLATFORM_NOT_EXISTS;
	    $zt = GameCollect::getInfoById($platform,$zt_id);
		$out = array();
		if($zt){
			//
			$views_num = rand(14,19);
			GameCollect::updateViewTimes($zt_id,$views_num);
			$out['tid'] = $zt['id'];
			$out['title'] = $zt['ztitle'];
			$out['img'] = Utility::getImageUrl($zt['litpic']);
			$out['updatetime'] = date('Y-m-d',$zt['addtime']);
			$out['viewcount'] = $zt['viewtimes'];
			$out['desc'] = $zt['description'];		
			$zt_games = GameCollectGames::getListById($platform,$zt_id);
			$gids = array();
			foreach($zt_games as $row){
				$gids[] = $platform=='ios' ? $row['gid'] : $row['agid'];
			}
			if($platform=='ios'){
				$games = IosGame::getMultiInfoById($gids,true);
			}else{
				$games = AndroidGame::getMultiInfoById($gids,true);
			}
		    
		    $games_opinion = array();//Opinion::getCountByGameIds($gids); 
		    $games_guide   = array();//Guide::getCountByGameIds($gids);
		    $games_video   = array();//GameVideo::getCountByGameIds($gids);
		    $games_comment = array();//Comment::getCountByGameIds($gids);
		    foreach($gids as $gid){
				if(!$gid || !isset($games[$gid])) continue;
				$game = array();
				$game['gid'] = $games[$gid]['id'];
				$game['img'] = Utility::getImageUrl($games[$gid]['ico']);
				$game['title'] = $games[$gid]['shortgname'];				
				$game['free'] = $games[$gid]['pricetype']==1 ? true : false;
				$game['limitfree'] = $games[$gid]['pricetype']==2 ? true : false;
				$game['summary'] = $games[$gid]['editorcomt'];
				$game['size'] = $games[$gid]['size'];
				$game['score'] = $games[$gid]['score'];
				$game['price'] = $platform=='ios' ? $games[$gid]['price'] : '';
				$game['oldprice'] = '';
				$game['video'] = isset($games_video[$gid]) && $games_video[$gid]>0 ? true : false;
				$game['guide'] = isset($games_guide[$gid]) && $games_guide[$gid]>0 ? true : false;
				$game['opinion'] = isset($games_opinion[$gid]) && $games_opinion[$gid]>0 ? true : false;
				$game['downcount'] = $games[$gid]['downtimes'];
				$game['commentcount'] = isset($games_comment[$gid]) ? $games_comment[$gid] : 0;
				$game['tname'] = '';
				
				$out['games'][] = $game;
			}
			$out['gamecount'] = count($gids);
			
			return $out;
		}
		return self::ERROR_SPECIAL_TOPIC_NOT_EXISTS;
	}
	
	/**
	 * 获取游戏类型列表
	 */
	public static function getGameTypeToList($platform)
	{
		$result = GameType::getList();
		$out = array();
		foreach($result as $row){
			$data = array();
			$data['id'] = $row['id'];
			$data['name'] = $row['typename'];
			$data['img'] = Utility::getImageUrl($row['img']);
			$out[] = $data;
		} 
		return $out;
	}
	
	/**
	 * 获取游戏标签列表
	 * 
	 * @param int $type_id
	 * 
	 */
	public static function getGameTagToList($platform,$type_id)
	{
	    $result = Tag::getListByType($type_id);
		$out = array();
		foreach($result as $row){
			$data = array();
			$data['tag'] = $row['tag'];
			$out[] = $data;
		}
		return $out;
	}
	
	public static function getGameTags($platform,$gids)
	{
		return GameTag::getGameTagsByGameIds($platform,$gids);
	}
	
	public static function getLastUpdate($platform,$pageIndex=1,$pageSize=10)
	{
		if(self::checkPlatform($platform)!==true) return self::ERROR_PLATFORM_NOT_EXISTS;
		$result = array();
		if($platform=='ios'){
			$search = array();
			$order = array('id'=>'desc');
			$result = IosGame::search($search,$pageIndex,$pageSize,$order);
		}else{
			$search = array();
			$order = array('id'=>'desc');
			$result = AndroidGame::search($search,$pageIndex,$pageSize,$order);
		}
		if($result['totalCount']==0) return array('result'=>array(),'totalCount'=>0);
		$gametype = GameType::getListToKeyValue();
	    $out = array();
	    foreach($result['result'] as $row){
	    	$game = array();
			$game['gid'] = $row['id'];			
			$game['gname'] = $row['shortgname'] ? : $row['gname'];
			$game['img'] = Utility::getImageUrl($row['ico']);
			$game['free'] = $row['pricetype']==1 ? true : false;
			$game['limitfree'] = $row['pricetype']==2 ? true : false;
			$game['price'] = $platform=='ios' ? $row['price'] : '';
			$game['isfirst'] = strval($row['isstarting']) ? true : false;
			$game['desc'] = '';//$row['shortcomt'] ? : $row['editorcomt'];			
			$game['adddate'] = date('Y-m-d',$row['addtime']);			
			$game['score'] = $row['score'];
			$game['typename'] = isset($gametype[$row['type']]) ? $gametype[$row['type']] : '';
			$game['language'] = self::$languages[$row['language']];
			$game['downcount'] = $row['downtimes'];
			$game['status'] = '0';
			$out[] = $game;
		}
		return array('result'=>$out,'totalCount'=>$result['totalCount']);
	}
	
	/**
	 * 检查平台参数
	 * 
	 */
	public static function checkPlatform($platform)
	{
		$valid = ($platform=='ios'||$platform=='android') ? true : false;
		return $valid;
	}
	
	public static function addGameReserve($platform,$uid,$game_id)
	{
		$result = UserGameReserve::addGameReserve($uid, $game_id);
		if(!$result) return self::ERROR_GAME_RESERVE_ERROR;
		return true;
	}
	
    /**
	 * 我的预约
	 * @param string $platform
	 * @param int $uid
	 * @param int $pageIndex
	 * @param int $pageSize
	 */
	public static function getUserGameReserveToList($platform,$uid,$pageIndex=1,$pageSize=10)
	{
		$all_gids = UserGameReserve::getGids($uid);
		
		if($all_gids){
			$pages = array_chunk($all_gids,$pageSize,false);
			$gids = isset($pages[$pageIndex-1]) ? $pages[$pageIndex-1] : array();
		}else{
			$gids = $all_gids;
		}		
		if(!$gids) return array('result'=>array(),'totalCount'=>0);
		
		$total = count($all_gids);
				
		$games = IosGame::getMultiInfoById($gids,true);
		$schemes = IosGameSchemes::getSchemesToKeyValue($gids);
		$out = array();
		$gametype = GameType::getListToKeyValue();
		foreach($gids as $gid){
			if(!isset($games[$gid])) continue;
			$game = $games[$gid];
			$tmp = array();
			$tmp['gid'] = $game['id'];
			$tmp['gname'] = $game['shortgname'];
			$tmp['img'] = Utility::getImageUrl($game['ico']);
			$tmp['free'] = $game['pricetype']==1 ? true : false;
			$tmp['limitfree'] = $game['pricetype']==2 ? true : false;
			$tmp['price'] = $platform=='ios' ? $game['price'] : '';
			$tmp['score'] = $game['score'];
			$tmp['typename'] = isset($gametype[$game['type']]) ? $gametype[$game['type']] : '';
			$tmp['language'] = self::$languages[$game['language']];
			$platform=='ios' && $tmp['downurl'] = $game['downurl'];
			$platform=='ios' && $tmp['schemes'] = isset($schemes[$game['id']]) ? $schemes[$game['id']] : '';
			$out[] = $tmp;
		}
		return array('result'=>$out,'totalCount'=>$total);
	}//public static function 
	
	/**
	 * 我的游戏
	 * @param string $platform
	 * @param int $uid
	 * @param int $pageIndex
	 * @param int $pageSize
	 */
	public static function getUserGameToList($platform,$uid,$pageIndex=1,$pageSize=10)
	{
		$all_gids = UserGame::getGids($uid);
		
		if($all_gids){
			$pages = array_chunk($all_gids,$pageSize,false);
			$gids = isset($pages[$pageIndex-1]) ? $pages[$pageIndex-1] : array();
		}else{
			$gids = $all_gids;
		}		
		if(!$gids) return array('result'=>array(),'totalCount'=>0);
		
		$total = count($all_gids);
				
		$games = IosGame::getMultiInfoById($gids,true);
		$schemes = IosGameSchemes::getSchemesToKeyValue($gids);
		$out = array();
		$gametype = GameType::getListToKeyValue();
		foreach($gids as $gid){
			if(!isset($games[$gid])) continue;
			$game = $games[$gid];
			$tmp = array();
			$tmp['gid'] = $game['id'];
			$tmp['gname'] = $game['shortgname'];
			$tmp['img'] = Utility::getImageUrl($game['ico']);
			$tmp['free'] = $game['pricetype']==1 ? true : false;
			$tmp['limitfree'] = $game['pricetype']==2 ? true : false;
			$tmp['price'] = $platform=='ios' ? $game['price'] : '';
			$tmp['score'] = $game['score'];
			$tmp['typename'] = isset($gametype[$game['type']]) ? $gametype[$game['type']] : '';
			$tmp['language'] = self::$languages[$game['language']];
			$platform=='ios' && $tmp['downurl'] = $game['downurl'];
			$platform=='ios' && $tmp['schemes'] = isset($schemes[$game['id']]) ? $schemes[$game['id']] : '';
			
			$out[] = $tmp;
		}
		return array('result'=>$out,'totalCount'=>$total);
	}
	
	/**
	 * 
	 */
	public static function getUserGameToIds($platform,$uid)
	{
		$gids = UserGame::getGids($uid);
		return $gids;
	}
	
	/**
	 * 
	 */
	public static function addUserGame($uid,$gids)
	{
		if(!$uid || !$gids) return self::ERROR_PARAMS_MISS;
		$data = array();
		if(is_array($gids)){
			foreach($gids as $gid){
				$data[] = array('uid'=>$uid,'game_id'=>$gid);
			}
		}else{
			$data[] = array('uid'=>$uid,'game_id'=>$gids);
		}
		if($data){
			$result = UserGame::addUserGame($data);
			return $result ? true : false;
		}
		return false;
	}
	
	public static function isExistsUserGame($uid,$gid)
	{
		$info = UserGame::db()->where('uid','=',$uid)->where('game_id','=',$gid)->first();
		return $info ? true : false;
	}
	
	public static function removeUserGame($uid,$gids)
	{
		$result = 0;
		if(is_array($gids)){
			$result = UserGame::db()->where('uid','=',$uid)->whereIn('game_id',$gids)->delete();
		}else{
			$result = UserGame::db()->where('uid','=',$uid)->where('game_id','=',$gids)->delete();
		}
		return $result ? true : false;
	}
	
	/**
	 * 
	 */
    public static function getUserGameReserveToIds($platform,$uid)
	{
		$gids = UserGameReserve::getGids($uid);
		return $gids;
	}
	
	public static function filterField($game,$filter='basic',$gametype=null)
	{		
		$fields = array(
		    'id','gname','shortgname','ico','type','score','downurl','size','language','pricetype','price','addtime','updatetime','oldprice',
		    'downtimes','commenttimes','description','editorcomt'
		);
		$out = array();
		foreach($game as $field=>$value){
			if(in_array($field,$fields)){
				switch($field)
				{
					case 'id':
						$out['gid'] = $value;
						break;
					case 'ico':
						$out[$field] = Utility::getImageUrl($value);
						break;
					case 'language':
						$out[$field] = self::$languages[$value];
						break;
					case 'addtime':
						$out['addtime'] = date('Y-m-d H:i:s',$value);
						break;
					case 'updatetime':
						$out['updatetime'] = date('Y-m-d H:i:s',$value);
						break;
					default:
						$out[$field] = $value;
						break;
				} 
			}elseif($filter=='full'){
			    $out[$field] = $value;
			}
			
		}
		if($gametype ){
			$out['typename'] = isset($gametype[$game['type']]) ? $gametype[$game['type']] : '';
		}
		unset($game);
		return $out;
	}
	
	public static function getGameDownloadCount($game_id,$platform)
	{
		$total = 0;
		if($platform=='ios'){
			$total = IosGame::db()->where('id','=',$game_id)->pluck('downtimes');
		}elseif($platform=='android'){
			$total = AndroidGame::db()->where('id','=',$game_id)->pluck('downtimes');
		}
		return array('game_id'=>$game_id,'total'=>$total);
	}
	
	public static function getMultiGameDownloadCount($game_ids,$platform)
	{
		if(!$game_ids) return array();
		if($platform=='ios'){
			$result = IosGame::db()->whereIn('id',$game_ids)->where('isdel','=',0)->select(self::raw('id as game_id,downtimes as total'))->get();
		}elseif($platform=='android'){
			$result = AndroidGame::db()->whereIn('id',$game_ids)->where('isdel','=',0)->select(self::raw('id as game_id,downtimes as total'))->get();
		}
		return $result;
	}
	
	/**
	 * 推荐玩伴
	 */
	public static function getRecommendUsers($uid,$platform,$pageIndex=1,$pageSize=10)
	{		
		$gids = UserGame::db()->where('uid','=',$uid)->lists('game_id');
		if(!$gids) return $out = array('result'=>array(),'totalCount'=>0);
		$hasMore = false;
		$skip = ($pageIndex-1) * $pageSize;
		$take = $pageSize+1;
		$res = UserGame::db()->whereIn('game_id',$gids)
		->select(UserGame::raw('uid,count(*) as total'))
		->groupBy('uid')
		->orderBy('total','desc')
		//->forPage($pageIndex,$pageSize)
		->skip($skip)->take($take)		
		->get();
		if(!$res) return $out = array('result'=>array(),'totalCount'=>0);
		$hasMore = count($res)>$pageSize ? true : false;
		if($hasMore==true) unset($res[$pageSize]);
		$uids = array();
		foreach($res as $row){
			$uids[] = $row['uid'];
		}
		$users = UserService::formatDataToKey(UserService::getMultiUserInfoByUids($uids,'short',$uid),'uid');
		$out = array();		
		foreach($res as $key=>$row){
			if(!isset($users[$row['uid']])) continue;
			$user = $users[$row['uid']];
			$user['gameCount'] = $row['total'];
			$out[] = $user;
		}
		return array('result'=>$out,'hasMore'=>$hasMore);
	}
	
	/**
	 * 获取游戏圈友数
	 * @param int $game_id
	 * @param string $platform
	 * 
	 * @return array 
	 */
	public static function getGameMemberCount($game_id,$platform)
	{
		$total = UserGame::getMemberCount($game_id);
		return array('game_id'=>$game_id,'total'=>$total);
	}
	
    public static function getGameMemberList($game_id,$platform,$pageIndex,$pageSize)
	{
		$uids = UserGame::db()->where('game_id','=',$game_id)->forPage($pageIndex,$pageSize)->lists('uid');
		$users = UserService::getMultiUserInfoByUids($uids,'short');
		if(!is_array($users)) return array();
		return $users;
	}
	
    /**
	 * 获取多个游戏圈友数
	 * @param int $game_id
	 * @param string $platform
	 * 
	 * @return array 
	 */
	public static function getMultiGameMemberCount($game_ids,$platform)
	{
		$result = UserGame::getMultiMemberCount($game_ids);
		return $result;
	}
	
	/**
	 * 添加游戏区服
	 */
	public static function addGameArea($uid,$type,$area_name,$game_id,$platform)
	{
		if(self::checkPlatform($platform)!==true) return self::ERROR_PLATFORM_NOT_EXISTS;
		if(!isset(GameArea::$AREA_TYPE_LIST[$type])) return self::ERROR_GAME_AREA_NOT_EXISTS;
		$data = array();
		$data['pid'] = 0;
		$data['game_id'] = $game_id;
		$data['uid'] = $uid;
		$data['type'] = $type;
		$data['typename'] = GameArea::$AREA_TYPE_LIST[$type];
		$data['area_name'] = $area_name;
		$data['is_open'] = 0;
		$success = GameArea::save($data);
		return $success ? true : false;
	}
	
	/**
	 * 游戏区服
	 */
	public static function getGameArea($game_id,$uid,$platform)
	{
		if(self::checkPlatform($platform)!==true) return self::ERROR_PLATFORM_NOT_EXISTS;
		$arealist = GameArea::getGameAreaList($game_id,$uid,$platform);
		if(!$arealist) return array();//self::ERROR_GAME_AREA_NOT_EXISTS;
		$out = array();
		$area = array();
		foreach($arealist as $row){
			if(!isset($area[$row['type']])){
				$area[$row['type']] = array(
				    'type'=>$row['type'],
				    'typename'=>$row['typename'],
				    'child'=>array(array('area_id'=>$row['id'],'area_name'=>$row['area_name']))
				);
			}else{
				$area[$row['type']]['child'][] = array('area_id'=>$row['id'],'area_name'=>$row['area_name']);
			}
		}
		
		foreach($area as $row){
			$out[] = array('type'=>$row['type'],'typename'=>$row['typename'],'child'=>$row['child']);
		}
		
		return $out;
	}
	
	/**
	 * 同区玩伴
	 */
	public static function getSameAreaUser($uid,$game_id,$area_id,$pageIndex=1,$pageSize=10)
	{
		$uids = UserGameArea::db()->where('game_id','=',$game_id)->where('area_id','=',$area_id)->orderBy('game_rolename','desc')->forPage($pageIndex,$pageSize)->distinct()->select('uid')->lists('uid');
		
		if($uids && is_array($uids)){
			$uids = array_unique($uids);
			$num = count($uids);
			if($num < $pageSize){
				$uids_b = UserGameArea::db()->where('game_id','=',$game_id)->distinct()->select('uid')->orderBy('game_rolename','desc')->forPage($pageIndex,$pageSize-$num)->lists('uid');
				$uids = array_merge($uids,$uids_b);
			}
			$uids = array_unique($uids);
			$num = count($uids);
			if($num < $pageSize){
				$uids_c = UserGame::db()->where('game_id','=',$game_id)->distinct()->select('uid')->forPage($pageIndex,$pageSize-$num)->lists('uid');
				$uids = array_merge($uids,$uids_c);
			}
		}else{
		    $uids = UserGameArea::db()->where('game_id','=',$game_id)->distinct()->select('uid')->forPage($pageIndex,$pageSize)->orderBy('game_rolename','desc')->lists('uid');
			if($uids && is_array($uids)){
				$uids = array_unique($uids);
				$num = count($uids);
				if($num < $pageSize){
					$uids_b = UserGame::db()->where('game_id','=',$game_id)->distinct()->select('uid')->forPage($pageIndex,$pageSize-$num)->lists('uid');
					$uids = array_merge($uids,$uids_b);
				}
			}else{
				$uids = UserGame::db()->where('game_id','=',$game_id)->distinct()->select('uid')->forPage($pageIndex,$pageSize)->lists('uid');				
			}
		}
		$uids = array_unique($uids);
		/*
		if(!$uids){
			$uids = UserGame::db()->forPage($pageIndex,$pageSize)->distinct()->select('uid')->lists('uid');
		}elseif($uids && count($uids)<$pageSize){
			$uids = array_unique($uids);
			$num = count($uids);
			if($num < $pageSize){
				$uids_d = UserGame::db()->forPage($pageIndex,$pageSize-$num)->distinct()->select('uid')->lists('uid');
				$uids = array($uids,$uids_d);
			}
		}
		*/
		if(!$uids) return array();
		$users = UserService::getMultiUserInfoByUids($uids,'short',$uid);
		if(!is_array($users)) return array();
		$game = IosGame::getInfoById($game_id);
		foreach($users as $key=>$user){
			$role = UserGameArea::db()->where('uid','=',$user['uid'])->where('game_id','=',$game_id)->first();	
			$area = $role ? GameArea::getInfo($role['area_id']) : null;		
			$user['game_card'] = array(
			    'gid'=>$game['id'],
			    'gname'=>$game['shortgname'],
			    'img'=>Utility::getImageUrl($game['ico']),
			    'game_rolename'=>$role ? $role['game_rolename'] : '',
			    'game_server'=>$area ? $area['typename'] : '',
			    'game_area'=>$area ? $area['area_name'] : ''
			);
			$users[$key] = $user;
		}
		return $users;
	}
	
	public static function getRandomAreaUser($uid,$size=2)
	{
		$res = UserGame::db()->forPage(1,100)->orderBy('id','desc')->distinct()->select('uid','game_id')->get();
		$uids = array();
		foreach($res as $key=>$row){
			if(in_array($row['uid'],$uids)){
				unset($res[$key]);
				continue;
			} 
			$uids[] = $row['uid'];
		}
		
		$rand_res = array_rand($res,$size);		
		//print_r($rand_res);exit;		
		$out = array();
		foreach($rand_res as $key){
			$row = $res[$key];
			$role = UserGameArea::db()->where('uid','=',$row['uid'])->where('game_id','=',$row['game_id'])->first();	
			$area = $role ? GameArea::getInfo($role['area_id']) : null;		
			$game = IosGame::getInfoById($row['game_id']);
			$user = UserService::getUserInfoByUid($row['uid'],'short',$uid);
			if(!is_array($user)) continue;
			$user['game_card'] = array(
			    'gid'=>$game['id'],
			    'gname'=>$game['shortgname'],
			    'img'=>Utility::getImageUrl($game['ico']),
			    'game_rolename'=>$role ? $role['game_rolename'] : '',
			    'game_server'=>$area ? $area['typename'] : '',
			    'game_area'=>$area ? $area['area_name'] : ''
			);
			$out[] = $user;
		}
		return $out;
	}
	
	/**
	 * 游戏基因
	 */
	public static function getUserGene($uid,$platform,$pageIndex,$pageSize)
	{
		$gids = UserGame::db()->where('uid','=',$uid)->distinct()->select('game_id')->orderBy('id','desc')->forPage($pageIndex,$pageSize)->lists('game_id');		
	    $games = IosGame::getMultiInfoById($gids,true);
		$schemes = IosGameSchemes::getSchemesToKeyValue($gids);
		$out = array();
		$gametype = GameType::getListToKeyValue();
		
		$area_ids = array();
		$_userarealist = UserGameArea::db()->where('uid','=',$uid)->get();
		$userarealist = array();
		foreach($_userarealist as $row){
			$area_ids[] = $row['area_id'];
			$userarealist[$row['game_id']] = $row;
		}
		$arealist = GameArea::getGameAreaListByIds($area_ids);
		//print_r($userarealist);exit;
		//print_r($arealist);exit;
		foreach($gids as $gid){
			if(!isset($games[$gid])) continue;
			$game = $games[$gid];
			$tmp = array();
			$tmp['gid'] = $game['id'];
			$tmp['gname'] = $game['shortgname'];
			$tmp['img'] = Utility::getImageUrl($game['ico']);
			$tmp['free'] = $game['pricetype']==1 ? true : false;
			$tmp['limitfree'] = $game['pricetype']==2 ? true : false;
			$tmp['price'] = $platform=='ios' ? $game['price'] : '';
			$tmp['score'] = $game['score'];
			$tmp['typename'] = isset($gametype[$game['type']]) ? $gametype[$game['type']] : '';
			$tmp['language'] = self::$languages[$game['language']];
			$platform=='ios' && $tmp['downurl'] = $game['downurl'];
			$platform=='ios' && $tmp['schemes'] = isset($schemes[$game['id']]) ? $schemes[$game['id']] : '';
			$tmp['areaId'] = isset($userarealist[$gid]['area_id']) ? $userarealist[$gid]['area_id'] : '';
			$tmp['game_rolename'] = isset($userarealist[$gid]['game_rolename']) ? $userarealist[$gid]['game_rolename'] : '';
			$tmp['game_area'] = isset($userarealist[$gid]) && isset($arealist[$userarealist[$gid]['area_id']]) ? $arealist[$userarealist[$gid]['area_id']]['typename']: '';
			$tmp['game_server'] = isset($userarealist[$gid]) && isset($arealist[$userarealist[$gid]['area_id']]) ? $arealist[$userarealist[$gid]['area_id']]['area_name']: '';
			
			$out[] = array('gid'=>$game['id'],'gname'=>$game['shortgname'],'img'=>Utility::getImageUrl($game['ico']),'game_card'=>$tmp);
		}
		return $out;
	}
	
	/**
	 * 
	 */
	public static function getUserGameAreaList($uid,$platform,$pageIndex=1,$pageSize=50,$gid=0)
	{
		$all_gids = UserGame::getGids($uid);
		if($gid){
			if(in_array($gid,$all_gids)){
				$all_gids = array($gid);
			}else{
				$all_gids = array();
			}
		}
		if($all_gids){
			$pages = array_chunk($all_gids,$pageSize,false);
			$gids = isset($pages[$pageIndex-1]) ? $pages[$pageIndex-1] : array();
		}else{
			$gids = $all_gids;
		}		
		if(!$gids) return array('result'=>array(),'totalCount'=>0);
		
		$total = count($all_gids);
				
		$games = IosGame::getMultiInfoById($gids,true);
		$schemes = IosGameSchemes::getSchemesToKeyValue($gids);
		$out = array();
		$gametype = GameType::getListToKeyValue();
		
		$area_ids = array();
		$_userarealist = UserGameArea::db()->where('uid','=',$uid)->get();
		$userarealist = array();
		foreach($_userarealist as $row){
			$area_ids[] = $row['area_id'];
			$userarealist[$row['game_id']] = $row;
		}
		$arealist = GameArea::getGameAreaListByIds($area_ids);
		//print_r($userarealist);exit;
		//print_r($arealist);exit;
		foreach($gids as $gid){
			if(!isset($games[$gid])) continue;
			$game = $games[$gid];
			$tmp = array();
			$tmp['gid'] = $game['id'];
			$tmp['gname'] = $game['shortgname'];
			$tmp['img'] = Utility::getImageUrl($game['ico']);
			$tmp['free'] = $game['pricetype']==1 ? true : false;
			$tmp['limitfree'] = $game['pricetype']==2 ? true : false;
			$tmp['price'] = $platform=='ios' ? $game['price'] : '';
			$tmp['score'] = $game['score'];
			$tmp['typename'] = isset($gametype[$game['type']]) ? $gametype[$game['type']] : '';
			$tmp['language'] = self::$languages[$game['language']];
			$platform=='ios' && $tmp['downurl'] = $game['downurl'];
			$platform=='ios' && $tmp['schemes'] = isset($schemes[$game['id']]) ? $schemes[$game['id']] : '';
			$tmp['areaId'] = isset($userarealist[$gid]['area_id']) ? $userarealist[$gid]['area_id'] : '';
			$tmp['game_rolename'] = isset($userarealist[$gid]['game_rolename']) ? $userarealist[$gid]['game_rolename'] : '';
			$tmp['game_area'] = isset($userarealist[$gid]) && isset($arealist[$userarealist[$gid]['area_id']]) ? $arealist[$userarealist[$gid]['area_id']]['typename']: '';
			$tmp['game_server'] = isset($userarealist[$gid]) && isset($arealist[$userarealist[$gid]['area_id']]) ? $arealist[$userarealist[$gid]['area_id']]['area_name']: '';
			
			$out[] = $tmp;
		}
		return array('result'=>$out,'totalCount'=>$total);
	}
	
	/**
	 * 保存用户区服信息
	 */
	public static function saveUserGameArea($uid,$game_id,$platform,$rolename,$area_id)
	{
		$exists = UserGameArea::db()
		->where('uid','=',$uid)
		->where('game_id','=',$game_id)
		->where('platform','=',$platform)
		->where('game_rolename','=',$rolename)
		->where('area_id','=',$area_id)
		->first();
		if($exists){
			$data = array('game_rolename'=>$rolename,'area_id'=>$area_id,'updatetime'=>time());
			return UserGameArea::db()->where('uid','=',$uid)->where('game_id','=',$game_id)->where('platform','=',$platform)->update($data) ? true : false;
		}else{
			$data = array(
			    'uid'=>$uid,
			    'game_id'=>$game_id,
			    'platform'=>$platform,
			    'game_rolename'=>$rolename,
			    'area_id'=>$area_id,
			    'ctime'=>time(),
			    'updatetime'=>time()
			);
			return UserGameArea::db()->insertGetId($data) ? true : false;
		}
	}
}