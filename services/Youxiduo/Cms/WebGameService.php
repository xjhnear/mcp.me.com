<?php
namespace Youxiduo\Cms;
use Youxiduo\Helper\Utility;

use Youxiduo\Cms\Model\Mtype;

use Illuminate\Support\Facades\Config;

use Youxiduo\Cms\Model\Addongame;
use Youxiduo\Cms\Model\Arctiny;
use Youxiduo\Cms\Model\Tagindex;
use Youxiduo\Cms\Model\Taglist;
use Youxiduo\Cms\Model\Arctype;
use Youxiduo\Cms\Model\Archives;

use Youxiduo\Base\BaseService;
use Youxiduo\Android\Model\Game as GameApk;
use Youxiduo\Android\Model\GamePlat;
use Youxiduo\Android\Model\GameTag;
use Youxiduo\Android\Model\GameType;
use Youxiduo\Game\Model\GameIos;
use Youxiduo\Game\Model\GamePicture;
use Youxiduo\V4\Game\GameService;
use Youxiduo\V4\Game\Model\GameBeta;
use Youxiduo\V4\Game\Model\AndroidGame;
use Youxiduo\V4\Game\Model\IosGame;

use Youxiduo\Android\Model\Giftbag;
use Yxd\Modules\Activity\GiftbagService;

class WebGameService extends BaseService
{
	/**
	 * 获取游戏信息
	 * @param int $game_id 网站端游戏ID
	 * @return array
	 */
	public static function getGameInfo($game_id)
	{
		$out = array();
		$id = trim($game_id,"z");
		$archive = Archives::db()->where('id','=',$id)->first();
		$addongame = Addongame::db()->where('aid','=',$id)->first();
		if(!$archive || !$addongame) return $out;
		$yxdid = $archive['yxdid'];
		$gid = 0;
		$game = null;
		$tags = array();
		if(strpos($yxdid,'g_')===0){
		    $gid = str_replace('g_','',$yxdid);
		    $game = GameService::getOneInfoById($gid,'ios');
		    $tags = GameService::getGameTags('ios',array($gid));
		}elseif(strpos($yxdid,'apk_')===0){
			$gid = str_replace('apk_','',$yxdid);
			$game = GameService::getOneInfoById($gid,'android');
			$tags = GameService::getGameTags('android',array($gid));
		}
		
		if($game){			
			$out['web_game_id'] = $game_id;
			$out['gname'] = $game['gname'];
			$out['shortgname'] = $game['shortgname'];
			$out['ico'] = $game['ico'];
			$out['typename'] = $game['typename'];			
			$tags = isset($tags[$gid]) ? $tags[$gid] : array();
			$out['tags'] = $tags;
			$out['score'] = $game['score'];
			$out['summary'] = $game['editorcomt'] ? : $game['description'];
			$out['url'] = 'http://www.youxiduo.com/game/' . strtolower($addongame['alphabet']) . '/';
		}
		if(!$out) return $out;
		return $out;
	}
	
	/**
	 * 获取游戏列表
	 * @param array $game_ids 多个网站端游戏ID数组
	 * @return array
	 */
	public static function getGameInfoList($game_ids)
	{
		$out = array();
		foreach($game_ids as $game_id){
		    $one = self::getGameInfo($game_id);
		    if($one){
		    	$out[] = $one;
		    }
		}
		return $out;
	}

	/**
	 *
	 * @param int $size
	 * @return array
	 */
	public static function getRandGameInfoList($size=2)
	{
		$res = self::searchGameList(array('ismake'=>1),1,$size,array(),false);
		$game_ids = array();
		if($res['totalCount']>0){
			foreach($res['result'] as $row)
			$game_ids[] = $row['id'];
			return self::getGameInfoList($game_ids);
		}
		return array();
	}
	
	public static function getGameGiftbagList($game_id)
	{
	    $out = array();
		$out = $list = self::getGiftbagList('ios',5);
		if($game_id==null) return $list;
		$archive = Archives::db()->where('id','=',$game_id)->first();
		if($archive && isset($archive['yxdid'])){
			$yxdid = $archive['yxdid'];
			$gid = 0;
			if(strpos($yxdid,'g_')===0){
			    $gid = str_replace('g_','',$yxdid);
			    $res = GiftbagService::getList($gid,1,2);
			    if($res['total']==0){
			    	$res = GiftbagService::getList(null,1,2);
			    }
			    $result = $res['result'];
			    $gids = array();
				foreach($result as $row){
					$gids[] = $row['game_id'];
				}
				$games = IosGame::getMultiInfoById($gids,true);
			
			    $out = array();
				foreach($result as $row){
					if(!isset($games[$row['game_id']])) continue;
					$game = $games[$row['game_id']];
					$tmp = array();
					$tmp['id'] = $row['id'];
					$tmp['mobile_game_id'] = $row['game_id'];
					$tmp['title'] = $row['title'];
					$tmp['ico'] = Utility::getImageUrl($game['ico']);
					$tmp['shortgname'] = $game['shortgname'];
					$tmp['total_num'] = $row['total_num'];
					$tmp['last_num'] = $row['last_num'];
					$out[] = $tmp;
				}
				if(count($out)<5){
					$out = array_merge($out,$list);
					return array_slice($out,0,5);
				}
				return $out;
			}elseif(strpos($yxdid,'apk_')===0){
				$gid = str_replace('apk_','',$yxdid);
			}
		}
		return $out;
	}
	
	/**
	 * 获取礼包列表
	 */
	public static function getGiftbagList($platform,$pageSize)
	{
		if($platform=='ios'){
			$res = GiftbagService::getList(null,1,$pageSize);
			$result = $res['result'];			
		}else{
			$result = Giftbag::getList(0,1,$pageSize);
		}
	    $gids = array();
		foreach($result as $row){
			$gids[] = $row['game_id'];
		}
		$games = $platform=='ios' ? IosGame::getMultiInfoById($gids,true) : AndroidGame::getMultiInfoById($gids,true);
			
		$out = array();
		foreach($result as $row){
			if(!isset($games[$row['game_id']])) continue;
			$game = $games[$row['game_id']];
			$tmp = array();
			$tmp['id'] = $row['id'];
			$tmp['mobile_game_id'] = $row['game_id'];
			$tmp['title'] = $row['title'];
			$tmp['ico'] = Utility::getImageUrl($game['ico']);
			$tmp['shortgname'] = $game['shortgname'];
			$tmp['total_num'] = $row['total_num'];
			$tmp['last_num'] = $row['last_num'];
			$out[] = $tmp;
		}
		return $out;
	}
	
	/**
	 * 获取开测表
	 */
	public static function getBetaTable($pageSize)
	{
		$start = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$gids = array();
		$agids = array();
		$hot_search['condition'] = array(array('field'=>'istop','logic'=>'=','value'=>0));		
		$hot_result = GameBeta::search($hot_search,1,$pageSize,array('addtime'=>'desc','id'=>'desc'));
		
		$over_search['condition'] = array(array('field'=>'addtime','logic'=>'<','value'=>$start));
		$over_result = GameBeta::search($over_search,1,$pageSize,array('addtime'=>'desc','id'=>'desc'));
		
		foreach($hot_result['result'] as $row){
			if($row['gid']>0) $gids[] = $row['gid'];
			if($row['agid']>0) $agids[] = $row['agid'];
		}
		
	    foreach($over_result['result'] as $row){
			if($row['gid']>0) $gids[] = $row['gid'];
			if($row['agid']>0) $agids[] = $row['agid'];
		}
		
		$gids = array_unique($gids);
		$agids = array_unique($agids);
		
		//$apk_games = AndroidGame::getMultiInfoById($gids,true);
		//$ios_games = IosGame::getMultiInfoById($gids,true);		
		$out = array();		
		foreach($hot_result['result'] as $row){
			$game = array();
			$game['gid'] = $row['gid'];
			$game['agid'] = $row['agid'];
			$game['gname'] = $row['title'];
			$game['state'] = $row['state'];
			$game['betadate'] = date('m-d',$row['addtime']);
			$game['is_android'] = $row['agid']>0 ? 'true' : 'false';
			$game['is_ios'] = $row['gid']>0 ? 'true' : 'false';
			$out['hot'][] = $game;
			
		}
		
	    foreach($over_result['result'] as $row){
			$game = array();
			$game['gid'] = $row['gid'];
			$game['agid'] = $row['agid'];
			$game['gname'] = $row['title'];
			$game['state'] = $row['state'];
			$game['betadate'] = date('m-d',$row['addtime']);
			$game['is_android'] = $row['agid']>0 ? 'true' : 'false';
			$game['is_ios'] = $row['gid']>0 ? 'true' : 'false';
			$out['over'][] = $game;
			
		}
		
		return $out;
	}
	
	/**
	 * 获取排行榜
	 */
	public static function getRankList($type)
	{
		$default = array(97266,107150,4479,57980,111833,88333,108027,110471,112934,113960);
		$ids = array();
		$config = \Yxd\Modules\System\SettingService::getConfig('www_home_rank');
		$keyname = '';
		switch($type){
			case 1:
				$keyname = 'ios_ids';
				break;
			case 2:
				$keyname = 'android_ids';
				break;
			case 3:
				$keyname = 'network_ids';
				break;
			case 4:
				$keyname = 'single_ids';
				break;
		}
		
		$ids = explode(',',$config['data'][$keyname]);
		if(!$ids) $ids = $default;
		$games = array();
		$archives = Archives::db()->whereIn('id',$ids)->get();
		foreach($archives as $row){
			$games[$row['id']] = $row;
		}
		$_games = Addongame::db()->whereIn('aid',$ids)->get();
		
		foreach($_games as $row){
			$games[$row['aid']] = array_merge($games[$row['aid']],$row);
		}
		$out = array();
		foreach($ids as $id){
			if(!isset($games[$id])) continue;
			$row = $games[$id];
			$game['web_game_id'] = $row['id'];
			$game['gname'] = $row['title'];
			$game['shortgname'] = $row['shorttitle'];
			$game['url'] = 'http://www.youxiduo.com/game/' . strtolower($row['alphabet']) . '/';
			$game['score'] = $row['score'] ? : 4;
			$game['ico'] = $row['litpic'];
			$game['typename'] = $row['gametype'];
			$game['downtimes'] = $row['downtimes'];
			$game['downurl'] = $row['downurl'];
			$out[] = $game;
		}
		return $out;
	}
	
	/**
	 * 搜索游戏
	 */
	public static function searchGameList($search,$pageIndex=1,$pageSize=10,$orderBy=array(),$join=false)
	{
		$count = self::buildSearchGame($search)->count();
		if($count==0) return array('result'=>array(),'totalCount'=>0);
		$tb = self::buildSearchGame($search);
		if(is_array($orderBy) && $orderBy){
			foreach($orderBy as $field=>$sort){
				$tb = $tb->orderBy($field,$sort);
			}
		}else{
			$tb = $tb->orderBy('id','desc');
		}
		$res = $tb->forPage($pageIndex,$pageSize)->get();
		if($join==true){
			$ids = array();
			foreach($res as $row){
				$ids[] = $row['id'];
			}
			if($ids){
			    $_addons = Addongame::db()->whereIn('aid',$ids)->get();
			    $addons = array();
			    foreach($_addons as $row){
			    	$addons[$row['aid']] = $row;
			    }
			    foreach($res as $key=>$one){
			    	if(isset($addons[$one['id']])){
			    		$res[$key] = array_merge($one,$addons[$one['id']]);
			    	}
			    }
			}
		}
		return array('result'=>$res,'totalCount'=>$count);
	}
	
	public static function buildSearchGame($search)
	{
		$tb = Archives::db()->where('typeid','=',4);
		if(isset($search['keyword']) && !empty($search['keyword'])){
			$tb = $tb->where('title','like','%'.$search['keyword'].'%');
		}
		if(isset($search['id']) && $search['id']){
			$tb = $tb->where('id','=',$search['id']);
		}
		
	    if(isset($search['in_ids']) && $search['in_ids']){
			$tb = $tb->whereIn('id',$search['in_ids']);
		}
		if(isset($search['ismake'])){
			$tb = $tb->where('ismake','=',1);
		}
		return $tb;
	}
	
}