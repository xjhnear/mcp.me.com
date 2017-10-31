<?php
namespace Yxd\Services\Cms;

use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Yxd\Services\TaskService;
use Yxd\Services\CreditService;
use Yxd\Modules\Core\CacheService;
use Yxd\Services\Service;
use Yxd\Models\Cms\Game;
use Yxd\Services\ForumService;

class GameService extends Service
{
	public static $languages = array('0'=>'未知','1'=>'中文','2'=>'英文','3'=>'其他');
	/**
	 * 首页最新
	 */
	public static function getHomeNew()
	{
		$section = 'game::lastupdate';
		$cachekey = 'game::lastupdate::home';
		$games = array();
		if(CLOSE_CACHE===false && CacheService::section($section)->has($cachekey)){
			$games = CacheService::section($section)->get($cachekey);
		}else{
			$games = self::dbCmsSlave()->table('games')
		       ->where('isdel','=',0)
		       ->forPage(1,12)
		       ->orderBy('addtime','desc')
		       ->get();
			CLOSE_CACHE===false && CacheService::section($section)->forever($cachekey,$games);
		}
		
		$out = array();
	    foreach($games as $index=>$row){
	    	$game = array();
			$game['gid'] = $row['id'];
			$game['title'] = $row['shortgname'] ? : $row['gname'];
			$game['img'] = GameService::joinImgUrl($row['ico']);
			$out[] = $game;
		}
		return $out;
	}
	
	/**
	 * 最新更新
	 */
	public static function getLastUpdate($page=1,$pagesize=10)
	{
		$total = self::dbCmsSlave()->table('games')->where('isdel','=',0)->count();
		$games = self::dbCmsSlave()->table('games')
		       ->where('isdel','=',0)
		       ->forPage($page,$pagesize)
		       ->orderBy('addtime','desc')
		       ->get();
	    $gids = array();
		foreach($games as $row){
			$gids[] = $row['id'];
		}
						
		$gametype = self::getGameTypeOption();      
		$out = array();
	    foreach($games as $index=>$row){
	    	$game = array();
			$game['gid'] = $row['id'];
			$game['img'] = GameService::joinImgUrl($row['ico']);
			$game['gname'] = $row['shortgname'] ? : $row['gname'];
			$game['free'] = $row['pricetype']==1 ? '1' : '0';
			$game['limitfree'] = $row['pricetype']==2 ? '1' : '0';
			$game['price'] = $row['price'];
			$game['isfirst'] = strval($row['isstarting']);
			$game['desc'] = '';//$row['shortcomt'] ? : $row['editorcomt'];			
			$game['adddate'] = date('Y-m-d',$row['addtime']);			
			$game['score'] = $row['score'];
			$game['type'] = isset($gametype[$row['type']]) ? $gametype[$row['type']] : '';
			$game['language'] = self::$languages[$row['language']];
			$game['status'] = '0';
			$out[] = $game;
		}
		return array('games'=>$out,'total'=>$total);
	}		
	
	/**
	 * 热门游戏
	 */
	public static function getHotGame($page=1,$pagesize=10,$uid=0)
	{
		if($page==1){
			//今日推荐
			$recommend = self::dbCmsSlave()->table('game_recommend')
			             ->select('games.*')
			             ->where('game_recommend.type','=','h')
			             ->leftJoin('games','game_recommend.gid','=','games.id')
			             ->orderBy('game_recommend.sort','desc')
			             ->get();
			$recommend = array();             		             
		}		
		$games = self::dbCmsSlave()->table('games')
			->where('flag','=','1')
            ->where('isdel','=','0')
            ->orderBy('isapptop','desc')
            //->orderBy('recommendsort','desc')
            ->orderBy('recommendtime','desc')
			->forPage($page,$pagesize)
			->get();
		if(isset($recommend) && is_array($recommend)){
			$len = count($recommend);
			$games = array_merge($recommend,array_slice($games,$len));
		}
		//
		$total = self::dbCmsSlave()->table('games')->where('isdel','=',0)->where('flag','=',1)->count();
		$gids = array();
		foreach($games as $row){
			$gids[] = $row['id'];
		}
		$game_credits = self::dbClubSlave()->table('game_credit')->whereIn('game_id',$gids)->lists('score','game_id');
								
		$out = array();
		foreach($games as $index=>$row){
			$out[$index]['gid'] = $row['id'];
			$out[$index]['img'] = GameService::joinImgUrl($row['ico']);
			$out[$index]['gname'] = $row['shortgname'];
			$out[$index]['free'] = $row['pricetype']==1 ? '1' : '0';
			$out[$index]['limitfree'] = $row['pricetype']==2 ? '1' : '0';
			$out[$index]['price'] = $row['price'];
			$out[$index]['oldprice'] = $row['oldprice'];
			$out[$index]['isfirst'] = strval($row['isstarting']);
			$out[$index]['moneyCount'] = isset($game_credits[$row['id']]) ? GameService::filterDownloadCredit($row['id'], $uid, $game_credits[$row['id']]) : 0;
			$out[$index]['desc'] = $row['shortcomt'];			
			$out[$index]['adddate'] = date('Y-m-d',$row['recommendtime'] ? : $row['addtime']);
			$out[$index]['score'] = $row['score'];
			$out[$index]['status'] = '1';
		}
		return array('games'=>$out,'total'=>$total); 
	}
	
	/**
	 * 经典必玩
	 */
	public static function getMustPlay($page=1,$pagesize=10)
	{
		$total = self::dbCmsSlave()->table('game_mustplay')->where('gid','>',0)->count();
		$games = self::dbCmsSlave()->table('game_mustplay')
		    ->where('gid','>',0)
		    ->orderBy('sort','desc')
		    ->orderBy('addtime','desc')
		    ->forPage($page,$pagesize)
		    ->get();
		$out = array();
		foreach($games as $index=>$row){
			$out[$index]['gid'] = $row['gid'];
			$out[$index]['title'] = $row['title'];
			$out[$index]['img'] = self::joinImgUrl($row['pic']);
		}
		return array('games'=>$out,'total'=>$total);
	}
	
	/**
	 * 特色专题列表
	 */
	public static function getGameCollect($page=1,$pagesize=10)
	{
		$total = self::dbCmsSlave()->table('zt')->where('apptype','!=',2)->count();
		$collect = self::dbCmsSlave()->table('zt')->where('apptype','!=',2)
		    ->select('id as tid','ztitle as title','litpic as img','addtime')
		    ->orderBy('isapptop','desc')
		    ->orderBy('addtime','desc')
		    ->forPage($page,$pagesize)
		    ->get();
		//
		
		$zt_ids = array();
		foreach($collect as $row){
			$zt_ids[] = $row['tid'];
		}
		$_collect_game = self::dbCmsSlave()->table('zt_games')->whereIn('zt_id',$zt_ids)->get();
		$collect_game = array();
		foreach($_collect_game as $row){
			$collect_game[$row['zt_id']][] = $row['gid'];
		}
		$game_ids = array();
		foreach($collect_game as $row){
			$game_ids = array_merge($game_ids,$row);
		}
		$games = self::getGamesByIds($game_ids);
		foreach($collect as $key=>$row){
			$gids = $collect_game[$row['tid']];
			foreach($gids as $gid){
				if(!$gid || !isset($games[$gid])) continue;
				$shortgame['gid'] = $games[$gid]['id'];
				$shortgame['img'] = self::joinImgUrl($games[$gid]['ico']);
			    $collect[$key]['games'][] = $shortgame;
			}
			$collect[$key]['img'] = self::joinImgUrl($row['img']);
			//$collect[$key]['gamecount'] = count($gids);
			$collect[$key]['updatetime'] = date('Y-m-d H:i:s',$row['addtime']);
		}
		return array('collect'=>$collect,'total'=>$total);
	}
	
	/**
	 * 特色专题内容页
	 */
	public static function getGameCollectDetail($tid)
	{
		//$total = DB::connection(self::$CONN)->table('zt')->count();
		$collect = self::dbCmsSlave()->table('zt')
		    ->where('id','=',$tid)
		    ->first();
        //
		$game_ids = self::dbCmsSlave()->table('zt_games')->where('zt_id','=',$tid)->where('gid','>',0)->orderBy('id','asc')->lists('gid');
		if(!$game_ids) return array();
		$games = self::getGamesByIds($game_ids);
		$out = array();
		$out['tid'] = $collect['id'];
		$out['title'] = $collect['ztitle'];
		$out['img'] = self::joinImgUrl($collect['litpic']);
		//$out['updatetime'] = date('Y-m-d H:i:s',$collect['addtime']);
		$out['desc'] = $collect['description'];
		$out['games'] = array();
		$gametype = self::getGameTypeOption();
		foreach($game_ids as $index=>$gid){
			if(!isset($games[$gid])) continue;
			$game = array();
			$game['gid'] = $games[$gid]['id'];
			$game['title'] = $games[$gid]['shortgname'];
			$game['img'] = GameService::joinImgUrl($games[$gid]['ico']);;
			$game['video'] = '0';//$games[$gid][''];
			$game['free'] = $games[$gid]['pricetype']==1 ? '1' : '0';
			$game['limitfree'] = $games[$gid]['pricetype']==2 ? '1' : '0';
			$game['size'] = $games[$gid]['size'];
			$game['score'] = $games[$gid]['score'];
			$game['oldprice'] = $games[$gid]['oldprice'];
			$game['price'] = $games[$gid]['price'];
			$game['guide'] = '0';//$games[$gid][''];
			$game['opinion'] = '0';//$games[$gid][''];
			$game['downcount'] = $games[$gid]['downtimes'];
			$game['commentcount'] = $games[$gid]['commenttimes'];
			$game['tname'] = isset($gametype[$games[$gid]['type']]) ? $gametype[$games[$gid]['type']]:'';
			$game['language'] = self::$languages[$games[$gid]['language']];
			$game['viewcount'] = $games[$gid]['viewtimes'];
			$game['commcount'] = GameCircleService::getGameCircleUserCount($gid);
			$game['postcount'] = GameCircleService::getGameCircleInfoCount($gid);
			$out['games'][] = $game;
			
			
		}
		//$out['gamecount'] = count($game_ids);
		$out['viewcount'] = $collect['viewtimes'];
		return $out;
	}
	
	/**
	 * 测试表
	 * @param int $type Tab类型,0：今日1：即将2：已经
	 */
	public static function getTestTable($type,$page=1,$pagesize=30)
	{
		if($type==0){
			return self::getTodayTestTable($page,$pagesize);
		}elseif($type==1){
			return self::getTomorrowTestTable($page,$pagesize);
		}elseif($type==2){
			return self::getYesterdayTestTable($page,$pagesize);
		}else{
			return self::getTodayTestTable($page,$pagesize);
		}		
	}
	//今日开测
	protected static function getTodayTestTable($page=1,$pagesize=10)
	{
		//$total = DB::connection(self::$CONN)->table('newgame')->count();
		$hotgame = self::dbCmsSlave()->table('newgame')->select('newgame.gid','newgame.title','newgame.state','newgame.openbeta','games.*','newgame.addtime','newgame.istop','newgame.isfirst')
		         ->leftJoin('games','newgame.gid','=','games.id')
		         ->where('newgame.gid','>',0)
		         ->where('newgame.istop','=',1)		         
		         ->orderBy('newgame.addtime','desc')
		         ->orderBy('newgame.id','desc')
		         ->get();
		//$hotgame_num = count($hotgame);
		
		$start = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$end = mktime(23,59,59,date('m'),date('d'),date('Y'));
		
		$today = self::dbCmsSlave()->table('newgame')->select('newgame.gid','newgame.title','newgame.state','newgame.openbeta','newgame.istop','newgame.isfirst','games.*','newgame.addtime')
		         ->leftJoin('games','newgame.gid','=','games.id')
		         ->where('newgame.gid','>',0)
		         ->where('newgame.istop','=',0)
		         ->where('newgame.addtime','=',$start)
		         ->orderBy('newgame.addtime','desc')
		         ->orderBy('newgame.id','desc')
		         ->get();
        //$today_total = DB::connection(self::$CONN)->table('newgame')->where('addtime','>=',$start)->where('addtime','<=',$end)->count();
        $other = array();        
        $tomorrow = array();//明天
        $future_week = array();//未来一周
        $bygone_week = array();//过去一周
        $tomorrow = self::dbCmsSlave()->table('newgame')->select('newgame.gid','newgame.title','newgame.state','newgame.openbeta','newgame.istop','newgame.isfirst','games.*','newgame.addtime')
		         ->leftJoin('games','newgame.gid','=','games.id')
		         ->where('newgame.gid','>',0)
		         ->where('newgame.istop','=',0)
		         ->where('newgame.addtime','=',$start+3600*24)
		         ->orderBy('newgame.addtime','desc')
		         ->orderBy('newgame.id','desc')
		         ->get();
		$future_week = self::dbCmsSlave()->table('newgame')->select('newgame.gid','newgame.title','newgame.state','newgame.openbeta','newgame.istop','newgame.isfirst','games.*','newgame.addtime')
		         ->leftJoin('games','newgame.gid','=','games.id')
		         ->where('newgame.gid','>',0)
		         ->where('newgame.istop','=',0)
		         ->where('newgame.addtime','>',$start+3600*24*1)
		         ->where('newgame.addtime','<',$start+3600*24*7)
		         ->orderBy('newgame.addtime','asc')
		         ->orderBy('newgame.id','desc')
		         ->get();
		$bygone_week = self::dbCmsSlave()->table('newgame')->select('newgame.gid','newgame.title','newgame.state','newgame.openbeta','newgame.istop','newgame.isfirst','games.*','newgame.addtime')
		         ->leftJoin('games','newgame.gid','=','games.id')
		         ->where('newgame.gid','>',0)
		         ->where('newgame.istop','=',0)
		         ->where('newgame.addtime','<',$start-3600*24)
		         ->where('newgame.addtime','>',$end-3600*24*8)
		         ->orderBy('newgame.addtime','desc')
		         ->orderBy('newgame.id','desc')
		         ->get();        
        $games = array_merge($hotgame,$today,$tomorrow,$future_week,$bygone_week);
        $pages = array_chunk($games,$pagesize,false);
		return array('games'=>isset($pages[$page-1])?$pages[$page-1]:array(),'total'=>count($games));
	}
	//即将开测
    protected static function getTomorrowTestTable($page=1,$pagesize=10)
	{
		$start = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$end = mktime(23,59,59,date('m'),date('d'),date('Y'));
		$total = self::dbCmsSlave()->table('newgame')->where('newgame.gid','>',0)->where('addtime','>',$end)->count();
		
		$games = self::dbCmsSlave()->table('newgame')->select('newgame.gid','newgame.title','newgame.state','newgame.openbeta','newgame.istop','newgame.isfirst','games.*','newgame.addtime')
		        
		         ->leftJoin('games',function($join){
		             $join->on('newgame.gid','=','games.id'); 
		         })
		         ->where('newgame.gid','>',0)
		         //->where('newgame.istop','=',0)
		         ->where('newgame.addtime','>',$end)
		         ->orderBy('newgame.addtime','asc')
		         ->orderBy('newgame.id','desc')
		         ->forPage($page,$pagesize)
		         ->get();
		return array('games'=>$games,'total'=>$total);
	}
	//已经开测
    protected static function getYesterdayTestTable($page=1,$pagesize=10)
	{
		$start = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$end = mktime(23,59,59,date('m'),date('d'),date('Y'));
		$total = self::dbCmsSlave()->table('newgame')->where('gid','>',0)->where('istop','=',0)->where('addtime','<',$start)
		         ->count();
		$games = self::dbCmsSlave()->table('newgame')->select('newgame.gid','newgame.title','newgame.state','newgame.openbeta','newgame.istop','newgame.isfirst','games.*','newgame.addtime')
		         ->leftJoin('games','newgame.gid','=','games.id')
		         ->where('newgame.gid','>',0)
		         //->where('newgame.istop','=',0)
		         ->where('newgame.addtime','<',$start)
		         ->orderBy('newgame.addtime','desc')
		         ->orderBy('newgame.id','desc')
		         ->forPage($page,$pagesize)
		         ->get();
		return array('games'=>$games,'total'=>$total);
	}
	
	/**
	 * 新游预告
	 */
	public static function getNewGame($page=1,$pagesize=10)
	{
		$section = 'commend::newgame';
		$cachekey_list = 'commend::newgame::list::' . $page;
		$cachekey_total = 'commend::newgame::total';
		if(CLOSE_CACHE===false && CacheService::section($section)->has($cachekey_total)){
			$total = CacheService::section($section)->get($cachekey_total);
		}else{
			$total = self::dbCmsSlave()->table('game_notice')->where('isshow','=',1)
				->where(function($query){
				    $query = $query->where('apptype','=',1)->orWhere('apptype','=',3);
				})
				->count();
			CLOSE_CACHE===false && CacheService::section($section)->forever($cachekey_total,$total);
		}
		if(CLOSE_CACHE===false && CacheService::section($section)->has($cachekey_list)){
			$games = CacheService::section($section)->get($cachekey_list);
		}else{
			$games = self::dbCmsSlave()->table('game_notice')->where('isshow','=',1)
			    ->where(function($query){
			        $query = $query->where('apptype','=',1)->orWhere('apptype','=',3);
			    })	    		    
			    ->orderBy('adddate','desc')
			    ->orderBy('sort','desc')
			    ->orderBy('addtime','desc')
			    ->forPage($page,$pagesize)
			    ->get();
			CLOSE_CACHE===false && CacheService::section($section)->forever($cachekey_list,$games);	
		}	
		return array('games'=>$games,'total'=>$total);
	}
	
	/**
	 * 玩家推荐应用
	 */
	public static function getPlayerRecommand($page=1,$pagesize=10)
	{
		$section = 'commend::app';
		$cachekey_list = 'commend::app::list::'.$page;
		$cachekey_total = 'commend::app::total';
		if(CLOSE_CACHE===false && CacheService::section($section)->has($cachekey_list)){
			$games = CacheService::section($section)->get($cachekey_list);
		}else{
		    $games = self::dbCmsSlave()->table('recommend')->where('apptype','=',1)->orWhere('apptype','=',3)->get();
		    CLOSE_CACHE===false && CacheService::section($section)->forever($cachekey_list,$games);
		}
		if(CLOSE_CACHE===false && CacheService::section($section)->has($cachekey_total)){
			$total = CacheService::section($section)->get($cachekey_total);
		}else{
		    $total = self::dbCmsSlave()->table('recommend')->where('apptype','=',1)->orWhere('apptype','=',3)->count();
		    CLOSE_CACHE===false && CacheService::section($section)->forever($cachekey_total,$total);
		}
		return array('games'=>$games,'total'=>count($games));
	}
	
	
	/**
	 * 游戏信息
	 */
	public static function getGameInfo($id)
	{
		$cachekey = 'game::games::info::' . $id;
		if(CLOSE_CACHE===false && CacheService::has($cachekey)){
			return CacheService::get($cachekey);
		}		
		$game = Game::getGameInfo($id);
        if($game){
			$game['language'] = self::$languages[$game['language']];
			$gametype = self::getGameTypeOption();
			$game['typename'] = isset($gametype[$game['type']]) ? $gametype[$game['type']] :  '';
			CLOSE_CACHE===false && CacheService::put($cachekey,$game,30); 
         }				
		return $game;
	}
	
	/**
	 * 游戏类型ID->Name键值对
	 */
	public static function getGameTypeOption()
	{
		$cachekey = 'game::game_type';
		$cache = null;//CacheService::get($cachekey);
		if(!$cache){
		    $cache = self::dbCmsSlave()->table('game_type')->orderBy('sort','desc')->lists('typename','id');
		    //CacheService::put($cachekey,$cache,30);
		}
		return $cache;
	}
	
	/**
	 * 游戏类型列表
	 */
	public static function getGameTypeList($hot=false,$uid=0)
	{
		$sum = self::dbCmsSlave()->table('games')->select(DB::raw('type,count(*) as total'))->where('isdel','=',0)->groupBy('type')->lists('total','type');
		$types = self::dbCmsSlave()->table('game_type')
		->orderBy('sort','desc')
		->orderBy('isapptop','desc')
		->orderBy('updatetime','desc')
		->get();
		$out = array();
		$index = 0;
		if($uid>0){
			$out[$index] = array('gtid'=>-1,'title'=>'我的游戏','gamecount'=>0);
		    $index++;
		}
		if($hot==true){
		    $out[$index] = array('gtid'=>0,'title'=>'热门游戏','gamecount'=>0);
		    $index++;
		}
		foreach($types as $row){
			$out[$index]['gtid'] = $row['id'];
			$out[$index]['title'] = $row['typename'];
			$out[$index]['gamecount'] = $sum[$row['id']];//$row[''];
			$index++;
		}
		
		return $out;
	}
	
	public static function getGameTags()
	{
		$tags = self::dbCmsSlave()->table('tag')->get();
		$gametype = self::getGameTypeOption();
		$out = array();
		foreach($tags as $row){
			$out[$row['typeid']]['gtid'] = $row['typeid'];
			$out[$row['typeid']]['tagName'] = $gametype[$row['typeid']];
			$out[$row['typeid']]['tags'][]['tag'] = $row['tag'];
		}
		$out_tags = array();
		foreach($gametype as $typeid=>$name){
			$out_tags[] = $out[$typeid];
		}
		return array_values($out_tags);
	}
	
	public static function getGameTagsByGameId($ids)
	{
		if(!$ids) return array();
		$_tags = self::dbCmsSlave()->table('games_tag')->whereIn('gid',$ids)->get();
		$tags = array();
		foreach($_tags as $row){
			$tags[$row['gid']][] = $row['tag'];
		}
		return $tags;
	}
	
	public static function getGuessGames($type,$size=100)
	{
		$games = self::dbCmsSlave()->table('games')->where('isdel','=',0)
		    ->where('type',$type)
		    ->orderBy('score','desc')
		    ->forPage(1,$size)
		    ->get();
		    
		if(count($games)>5){
			$keys = array_rand($games,5);
			$out = array();
			foreach($keys as $key){
				$out[]= $games[$key];
			}
			return $out;
		}else{
			return $games;
		}
	}
	
	public static function getGamesByType($tid,$page=1,$pagesize=15,$uid=0,$addtype=0,$is_forum=0)
	{	
	    if($is_forum == 1){
			$forum_gids = ForumService::getOpenForumGids();
			if($forum_gids && count($forum_gids)){
				$total = self::dbCmsSlave()->table('games')->where('isdel','=',0)->whereIn('id',$forum_gids)
			    ->where('type',$tid)
		    	->count();
			}else{
				$total = 0;
			}
		}else{
			$total = self::dbCmsSlave()->table('games')->where('isdel','=',0)
		    ->where('type',$tid)
		    ->count();
		}	
		
		$_games = self::dbCmsSlave()->table('games')->where('isdel','=',0)
		    ->where('type',$tid)
		    ->orderBy('score','desc')
		    ->forPage($page,$pagesize)
		    ->get();
		$out = array();
	    
		if($uid){
			$my_game_ids = GameCircleService::getMyGameIds($uid);  
		}
		$gametype = self::getGameTypeOption();
		foreach($_games as $index=>$row){
			if($is_forum==1 && !in_array($row['id'],$forum_gids)) continue;
			$game = array();
			$game['gid'] = $row['id'];
			$game['title'] = $row['shortgname'];
			$game['img'] = self::joinImgUrl($row['ico']);
			$game['star'] = $row['score'];
			$game['type'] = $gametype[$row['type']];
			$game['language'] = self::$languages[$row['language']];
			$game['incycle'] = isset($my_game_ids) && in_array($row['id'],$my_game_ids) ? '1' : '0';
			$out[] = $game;
			
		}
		return array('games'=>$out,'total'=>$total);
	}
	
	public static function getGamesByIds($ids)
	{
		if(!$ids) return array();
		$_games = self::dbCmsSlave()->table('games')->whereIn('id',$ids)->where('isdel','=',0)->get();
		$games = array();
		foreach($_games as $row){
			$games[$row['id']] = $row;
		}
		return $games;
		
	}
	
	public static function getHotSearchGames()
	{
		return self::dbCmsSlave()->table('game_recommend')
			             ->select('games.*')
			             ->where('game_recommend.type','=','h')
			             ->leftJoin('games','game_recommend.gid','=','games.id')
			             ->orderBy('game_recommend.sort','desc')
			             ->get();
	}
	
	/**
	 * 搜索结果
	 */
	public static function search($keyword,$page=1,$pagesize=15,$uid=0,$isforum=0)
	{
		if(empty($keyword)){
			return array('games'=>array(),'total'=>0);
		}
		
		$game_ids = self::dbCmsSlave()->table('games')->where('isdel','=',0)->where('shortgname','like','%'.$keyword . '%')->select('id')->lists('id');	    
		if($isforum==1){
			$forum_gids = ForumService::getOpenForumGids();
			$game_ids = array_intersect($game_ids,$forum_gids);
		}	
		$total = count($game_ids);
	    if(!$game_ids){
			return array('games'=>array(),'total'=>0);
		}
		$games = self::dbCmsSlave()->table('games')->whereIn('id',$game_ids)->where('isdel','=',0)->forPage($page,$pagesize)->orderBy('score','desc')->get();
		
		return array('games'=>$games,'total'=>$total);
	}
	
	/**
	 * 搜索匹配
	 */
	public static function searchTip($keyword,$isforum=0)
	{
	    if(empty($keyword)){
			return array('games'=>array(),'total'=>0);
		}
		$game_ids = self::dbCmsSlave()->table('games')->where('isdel','=',0)->where('shortgname','like','%'.$keyword . '%')->select('id')->lists('id');	    
		if($isforum==1){
			$forum_gids = ForumService::getOpenForumGids();
			$game_ids = array_intersect($game_ids,$forum_gids);
		}	
		$total = count($game_ids);
		if(!$game_ids){
			return array('games'=>array(),'total'=>0);
		}
		$games = self::dbCmsSlave()->table('games')->whereIn('id',$game_ids)->where('isdel','=',0)->forPage(1,10)->orderBy('score','desc')->get();		
		
		return array('games'=>$games,'total'=>$total);
	}
	
	/**
	 * 游戏识别码
	 */
	public static function schemesurl()
	{
		$tb = self::dbCmsSlave()->table('games_schemes');
		$games = $tb->select('gid','schemesurl')->where('schemesurl','!=','')->get();
		$out = array();
		foreach($games as $index=>$row){
			$game = array();
			$game['gid'] = $row['gid'];
			$schemesurl = explode(',',$row['schemesurl']);
			if(!isset($schemesurl[0])) continue;
			$schemeurl = $schemesurl[0];
			foreach($schemesurl as $url){
				if(strpos($url,'wx')===0) $schemeurl = $url;
			}						
			$game['schemesurl'] = $schemeurl;
			$out[] = $game;
		}
		return array('result'=>$out);
	}
	
	/**
	 * 
	 */
	public static function getGamesByTag($tag)
	{
		$gids = self::dbCmsSlave()->table('games_tag')
		->where('tag','=',$tag)
		->orderBy('id','desc')
		->forPage(1,50)
		->lists('gid');
		if(count($gids)>6){
			$gids = array_rand($gids,6);
		}
		$games = self::getGamesByIds($gids);
		
		return $games;
	}
	
	/**
	 * 
	 */
	public static function getRelationGamesByID($game_id)
	{
		$game = self::getGameInfo($game_id);
		$gids = self::dbCmsSlave()->table('games')->where('type','=',$game['type'])->where('isdel','=',0)->where('id','!=',$game_id)->forPage(1,50)->lists('id');
		if(count($gids)>3){
			$gids = array_rand($gids,3);
		}elseif(!$gids){
			return array();
		}
		$games = self::getGamesByIds($gids);
		
		return $games;				
	}
	
	
	public static function getExpeditionTearm($page=1,$pagesize=10)
	{
		$total = self::dbCmsSlave()->table('game_expedition')->count();
		$games = self::dbCmsSlave()->table('game_expedition')->select('game_expedition.*','games.ico')->leftJoin('games','game_expedition.gid','=','games.id')
		->orderBy('sort','asc')->orderBy('addtime','desc')->forPage($page,$pagesize)->get();
		return array('games'=>$games,'total'=>$total);
	}
	
	/**
	 * 过滤下载游币奖励
	 */
	public static function filterDownloadCredit($game_id,$uid,$default=0)
	{		
		//普通下载奖励
		$task = self::dbClubSlave()->table('task')->where('action','=','download')->first();
		$dl_score = 0;$dl_experience = 0;		
		if($task && isset($task['reward'])){
			$credit = json_decode($task['reward'],true);
			$cond = json_decode($task['condition'],true);
			if(isset($cond['closed']) && $cond['closed']==1){
				$dl_score = 0;
				$dl_experience = 0;
			}else{
				$dl_score = (int)$credit['score'];
				$dl_experience = (int)$credit['experience'];
			}
		}
		
		//广告奖励
		$ad_score = 0;$ad_experience = 0;
		$game_credit = self::dbClubSlave()->table('game_credit')->where('game_id','=',$game_id)->orderBy('id','desc')->first();
		if($game_credit && isset($game_credit['score'])){
			$ad_score = (int)$game_credit['score'];
			//$ad_experience = (int)$game_credit['experience'];
		}
		
		if($uid>0){
			//普通下载
			//$game_download_times = (int)self::redis()->get('game::game_download_' . $game_id . '_' . $uid . '_times');
			//$game_download_totaltimes = (int)self::redis()->get('game::game_download_'.$uid.'_totaltimes');
			$start = mktime(0,0,0,date('m'),date('d'),date('Y'));
			$game_download_times = self::dbClubSlave()->table('game_download_count')->where('game_id','=',$game_id)->where('uid','=',$uid)->count();
			$game_download_totaltimes = self::dbClubSlave()->table('game_download_count')->where('uid','=',$uid)->where('lastupdatetime','>',$start)->count();
			if($game_download_times > 0){
				$dl_score= 0;
			}
			if($game_download_totaltimes>3){
				$dl_score = 0;
			}
			//广告下载
			//$game_ad_download_times = (int)self::redis()->get('game::game_ad_download_' . $game_credit['id'] . '_' . $uid . '_times');
			$game_ad_download_times = self::dbClubSlave()->table('game_download_adv_count')->where('adv_id','=',$game_credit['id'])->where('uid','=',$uid)->count();
			if($game_ad_download_times > 0){
				$ad_score = 0;
			}
		}
		return $dl_score+$ad_score;
	}
	
	/**
	 * 下载游币奖励
	 */
	public static function doDownloadCredit($game_id,$uid)
	{
		return self::_doDownloadCredit($game_id, $uid);
		/*
		if(!$uid){
			return 0;
		}
		
	    //判断奖励次数
		$end = mktime(23,59,59,date('m'),date('d'),date('Y'));
		$expire = $end - time();
		
		self::redis()->incr('game::game_download_'.$game_id . '_' . $uid . '_times');

		//总数
		self::redis()->incr('game::game_download_'.$uid . '_totaltimes');
		self::redis()->expire('game::game_download_'.$uid . '_totaltimes',$expire);
		$game_download_times = (int)self::redis()->get('game::game_download_' . $game_id . '_' . $uid . '_times');
		$game_download_totaltimes = (int)self::redis()->get('game::game_download_'.$uid.'_totaltimes');
		//条件限制
		$dl_score_flag = true;
		$ad_score_flag = true;
		if($game_download_times>1){
			$dl_score_flag = false;
			//return 1;
		}

		if($game_download_totaltimes>3){
			//return 2;
		}
					    		
		//广告奖励
		$ad_score = 0;$ad_experience = 0;
		$game_credit = self::dbClubSlave()->table('game_credit')->where('game_id','=',$game_id)->first();
		if($game_credit && isset($game_credit['score'])){
			$ad_score = (int)$game_credit['score'];
			self::redis()->incr('game::game_ad_download_' . $game_credit['id'] . '_' . $uid . '_times');
		}
		$score = 0;
		//任务奖励
		if($dl_score_flag==true){
			$dl_score = TaskService::doDownloadGame($uid);
			is_numeric($dl_score) && $score = $dl_score;
		}
		//广告奖励				
        if($ad_score){
        	$game_ad_download_times = (int)self::redis()->get('game::game_ad_download_' . $game_credit['id'] . '_' . $uid . '_times');
        	if($game_ad_download_times <= 1){
				$info = '下载游戏奖励' . $ad_score . '游币';
			    CreditService::handOpUserCredit($uid, $ad_score,0,'download_game',$info);
			    $score = (isset($dl_score)&&is_numeric($dl_score)) ? $dl_score + $ad_score : $ad_score;
        	}
		}
				
		return array('score'=>$score);
		*/
	}

    protected static function _doDownloadCredit($game_id,$uid)
    {
        if(!$uid){
            return 0;
        }

        //判断奖励次数
        $end = mktime(23,59,59,date('m'),date('d'),date('Y'));
        $start = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $expire = $end - time();

        $game_download_times = self::dbClubSlave()->table('game_download_count')->where('game_id','=',$game_id)->where('uid','=',$uid)->count();
        $game_download_totaltimes = self::dbClubSlave()->table('game_download_count')->where('uid','=',$uid)->where('lastupdatetime','>',$start)->count();        
        
        if($game_download_times==0){
        	self::dbClubMaster()->table('game_download_count')->insert(array('game_id'=>$game_id,'uid'=>$uid,'times'=>1,'lastupdatetime'=>time()));
        }
        
        //条件限制
        $dl_score_flag = true;
        $ad_score_flag = true;
        if($game_download_times>0){
            $dl_score_flag = false;
            //return 1;
        }

        //广告奖励
        $ad_score = 0;$ad_experience = 0;
        $game_credit = self::dbClubSlave()->table('game_credit')->where('game_id','=',$game_id)->first();
        if($game_credit && isset($game_credit['score'])){
            $ad_score = (int)$game_credit['score'];
           // self::redis()->incr('game::game_ad_download_' . $game_credit['id'] . '_' . $uid . '_times');
        }
        $score = 0;
        //任务奖励
        if($dl_score_flag==true){
            $dl_score = TaskService::doDownloadGame($uid);
            is_numeric($dl_score) && $score = $dl_score;
        }
        //广告奖励
        if($ad_score){
        	
            //$game_ad_download_times = (int)self::redis()->get('game::game_ad_download_' . $game_credit['id'] . '_' . $uid . '_times');
            $game_ad_download_times = self::dbClubSlave()->table('game_download_adv_count')->where('adv_id','=',$game_credit['id'])->where('uid','=',$uid)->count();
            
            if($game_ad_download_times == 0){
                $info = '下载游戏奖励' . $ad_score . '游币';
                CreditService::handOpUserCredit($uid, $ad_score,0,'download_game',$info);
                $score = (isset($dl_score)&&is_numeric($dl_score)) ? $dl_score + $ad_score : $ad_score;
                self::dbClubMaster()->table('game_download_adv_count')->insert(
                    array('adv_id'=>$game_credit['id'],'uid'=>$uid,'times'=>1,'lastupdatetime'=>time())
                );
            }
        }

        return array('score'=>$score);

    }
	
	public static function download($game_id,$uid=0)
	{
		if(!$game_id) return false;
		self::updateDownloadCountByRemote($game_id);
		$num = rand(1,5);
		$sql = "update m_games set downtimes=downtimes+".$num.",weekdown=weekdown+".$num.",realdown=realdown+1 where id=".$game_id;
		//self::dbCmsMaster()->table('games')->where('id','=',$game_id)->update(DB::raw($sql));
		self::dbCmsMaster()->update($sql);
		$tb = self::dbCmsMaster()->table('game_download_count')->where('gid','=',$game_id)->where('down_time','=',mktime(0,0,0,date('m'),date('d'),date('Y')));
		if($tb->count()>0){
			$tb->increment('number');
		}else{
			$data = array(
			    'gid'=>$game_id,
			    'down_time'=>mktime(0,0,0,date('m'),date('d'),date('Y')),
			    'number'=>1
			);
			self::dbCmsMaster()->table('game_download_count')->insertGetId($data);
		}
	}
	
	public static function updateDownloadCountByRemote($game_id)
	{
		$uri = 'module_data/game_download_count';
		$url = Config::get('app.module_data_url') . $uri;
		$params = array('game_id'=>$game_id,'platform'=>1);
		$result = \CHttp::request($url,$params,'POST');
		if(!is_array($result)){			
			$data = array('game_id'=>$game_id,'ctime'=>time());
			self::dbCmsMaster()->table('game_download_count_retry')->insert($data);
			Log::error($result);
		}
	}
	
	public static function getDownloadCountByRemote($game_id,$default=0)
	{
		$uri = 'module_data/game_download_count';
		$url = Config::get('app.module_data_url') . $uri;
		$params = array('game_id'=>$game_id,'platform'=>1);
		$result = \CHttp::request($url,$params,'GET');
		if(isset($result['downloadDisplayCount'])){
			return (int)$result['downloadDisplayCount'];
		}
		return $default;
	}
}