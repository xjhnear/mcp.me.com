<?php
namespace Yxd\Services\Cms;

use Yxd\Modules\Core\CacheService;

//use Illuminate\Support\Facades\DB;
use Yxd\Services\Service;
use Yxd\Models\Cms\Game;
use Yxd\Services\ForumService;
use Yxd\Services\Cms\GameCircleService;

class AdvService extends Service
{	
	const DETAIL_ADV_NEWS = 1;
	const DETAIL_ADV_GUIDE = 2;
	const DETAIL_ADV_OPINION = 3;
	const DETAIL_ADV_NEWGAME = 4;
	const DETAIL_ADV_TOPIC = 0;
	
	/**
	 * 广告信息
	 */
	public static function getAdvInfo($appname,$version,$type)
	{
		$appname = '';
		$adv = self::dbCmsSlave()->table('appadv')
		->where('appname','=',$appname)
		->where('version','=',$version)
		->where('type','=',$type)
		->first();
		
		return $adv;
	}
	/**
	 * 过滤通用参数
	 */
	protected static function filterCommonParams($adv)
	{
		if(!$adv) return array();
		$out = array();
		$out['advtype'] = $adv ? 1 : 0;
		$out['staturl'] = $adv['url'];
		$out['advid'] = $adv['aid'];
		$out['location'] = $adv['location'];
		$out['tosafari'] = $adv['tosafari'];
		$out['sendmac'] = $adv['sendmac'];
		$out['sendidfa'] = $adv['sendidfa'];
		$out['sendudid'] = $adv['sendudid'];
		$out['sendos'] = $adv['sendos'];
		$out['sendplat'] = $adv['sendplat'];
		$out['sendactive'] = $adv['sendactive'];
		$out['downurl'] = $adv['downurl'];
		return $out;
	}
	
	/**
	 * 获取启动页广告
	 */
	public static function getLaunch($appname,$version,$isiphone5)
	{
		$appname = '';
		$iphone = $isiphone5 ? '5' : '4';
		$cachekey = 'adv::launch::' . $appname . '::' . $version .'::' . $iphone;
		if(CLOSE_CACHE===false && CacheService::has($cachekey)){
		    $out = CacheService::get($cachekey);
		}else{
			$adv = AdvService::getAdvInfo($appname,$version,5);		
		    $out = self::filterCommonParams($adv);
		    $litpic = $isiphone5 ? $adv['bigpic'] : $adv['litpic'];
		    $out && $out['litpic'] = AdvService::joinImgUrl($litpic);
		    CLOSE_CACHE===false && CacheService::forever($cachekey,$out);
		}
		
				
		return $out;
	}
	
	/**
	 * 弹窗广告
	 */
	public static function getOpenWin($appname,$version,$entrance,$appversion)
	{
		$real_appname = $appname;
		$appname = '';
		$type = $entrance ? 6 : 3;
		$out = array();
		$cachekey = 'adv::popwin::' . $appname . '::' . $version .'::' . $type;
		if(CLOSE_CACHE===false && CacheService::has($cachekey)){
			$out = CacheService::get($cachekey);
		}else{
		    $appconfig = AppService::getConfig($real_appname,$appversion,true);		
			if(!$entrance){
				$type = 3;//游戏详情页
				if(!isset($appconfig['append']['adv']) || (int)$appconfig['append']['adv'] == 0){
					return array();
				}
			}else{
				$type = 6;//首页
				if(!isset($appconfig['append']['gg']) || (int)$appconfig['append']['gg'] == 0){
					return array();
				}
				$advpos = self::dbCmsSlave()->table('advpos')
					->where('appname','=',$appname)
					->where('version','=',$version)
					->where('postype','=',2)
					->orderBy('id','desc')
					->first();
			    if($advpos){
			    	$out['gname'] = $out['title'] = $advpos['title'];
			    	$out['words'] = $advpos['words'];
					$out['type'] = $advpos['type'];
					$out['linkid'] = $advpos['link_id'];
					$out['litpic'] = $out['img'] = AdvService::joinImgUrl($advpos['litpic']);
			    }
			}
			
			$adv = AdvService::getAdvInfo($appname,$version,$type);
			if($adv){
				$out = self::filterCommonParams($adv);
				if($out){
					$out['gname'] = $adv['advname'];		
					$out['litpic'] = AdvService::joinImgUrl($adv['litpic']);
					$out['words'] = $adv['title'];
				}
				CLOSE_CACHE===false && CacheService::forever($cachekey,$out);
			}		
		}
		return $out;
	}
	
	
	/**
	 * 首页幻灯
	 */
	public static function getHomeSlide($appname,$version)
	{
		$appname = '';
		//轮播推荐位
		$cachekey_pos = 'commend::'.$appname . '::'.$version . '::slide';
		$advpositions = array();
		if(CLOSE_CACHE===false && CacheService::has($cachekey_pos)){
			$advpositions = CacheService::get($cachekey_pos);
		}else{
		    $advpositions = self::dbCmsSlave()->table('adv')
		       ->where('version','=',$version)
		       ->where('appname','=',$appname)
		       ->forPage(1,5)
		       ->orderBy('sort','desc')
		       ->get();
		    CLOSE_CACHE===false && CacheService::forever($cachekey_pos,$advpositions);
		}		
        $out = array();
	    foreach($advpositions as $index=>$row){
			$ad = array();
			//$ad['aid'] = $row['id'];
			$ad['title'] = $row['title'];
			$ad['type'] = $row['type'];
			$ad['linkid'] = $row['link_id'];
			$ad['img'] = AdvService::joinImgUrl($row['litpic']);	
            $out[] = $ad;			
		} 
		 
		//轮播广告位
		$cachekey_adv = 'adv::'.$appname . '::'.$version . '::slide';
		$advs = array();
		if(CLOSE_CACHE===false && CacheService::has($cachekey_adv)){
			$advs = CacheService::get($cachekey_adv);
		}else{
		    $advs = self::dbCmsSlave()->table('appadv')
		        ->where('type','=',1)
		        ->where('appname','=',$appname)
		        ->where('version','=',$version)
		        ->orderBy('location','asc')
		        ->get();
		    CLOSE_CACHE===false && CacheService::forever($cachekey_adv,$advs);
		}
        $position_advs = array();
		foreach($advs as $key=>$row){
            $adv = self::filterCommonParams($row);
            $adv['title'] = $row['advname'];
            $adv['img'] = self::joinImgUrl($row['litpic']);
        	$position_advs[$row['location']-1] = $adv;
        }
        
        foreach($out as $key=>$row){
        	if(isset($position_advs[$key])){        		
        		$out[$key] = $position_advs[$key];
        		$out[$key]['type'] = $row['type'];
        		$out[$key]['linkid'] = $row['linkid'];
        	}
        }
		
		return array_values($out);
	}
	
	/**
	 * 首页广告条
	 */
	public static function getHomeBar($appname,$appversion,$version)
	{		
	    $appconfig = AppService::getConfig($appname,$appversion,true);
	    
		if(!isset($appconfig['append']['bar']) || (int)$appconfig['append']['bar'] == 0){
			return array();
		}
		
		//$section = 'commend::' . $appname . '::' . $version.'::advpos::1';
		//$cachekey_pos = 'commend::'
		$appname = '';
		$advpos = self::dbCmsSlave()->table('advpos')
			->where('appname','=',$appname)
			->where('version','=',$version)
			->where('postype','=',1)
			->orderBy('id','desc')
			->first();
		$out = array();
		$cachekey = 'adv::'.$appname . '::'.$version . '::homebar';
		if(CLOSE_CACHE===false && CacheService::has($cachekey)){
			$adv = CacheService::get($cachekey);
		}else{
		    $adv = self::dbCmsSlave()->table('appadv')
		        ->where('location','=',25)
		        ->where('appname','=',$appname)
		        ->where('version','=',$version)
		        ->first();
		    CLOSE_CACHE===false && CacheService::forever($cachekey,$adv);    
		}
		if(!$adv && $advpos) {
			$out['title'] = $advpos['title'];
			$out['type'] = $advpos['type'];
			$out['linkid'] = $advpos['link_id'];
			$out['img'] = AdvService::joinImgUrl($advpos['litpic']);
		}else{		
			$out = self::filterCommonParams($adv);
			$out && $out['img'] = AdvService::joinImgUrl($adv['litpic']);
		}	    
		return $out ? array($out) : array(); 
	}
	
	/**
	 * 首页热门游戏
	 */
	public static function getHomeHotGame($appname,$version,$uid=0)
	{
		$appname = '';
		//今日推荐
		$section = 'commend::'.$appname . '::'.$version . '::hotrecommend';
		$cachekey_pos = 'commend::'.$appname . '::'.$version . '::hotrecommend::home';
		if(CLOSE_CACHE===false && CacheService::section($section)->has($cachekey_pos)){
			$games = CacheService::section($section)->get($cachekey_pos);
		}else{
		    $games = self::dbCmsSlave()->table('games')
		             ->where('flag','=','1')
		             ->where('isdel','=','0')
		             ->orderBy('isapptop','desc')
		             ->orderBy('recommendsort','desc')
		             ->orderBy('addtime','desc')
		             ->forPage(1,12)
		             ->get();
		    CLOSE_CACHE===false && CacheService::section($section)->forever($cachekey_pos,$games);
		}
		//
		$gids = array();
		foreach($games as $row){
			$gids[] = $row['id'];
		}
		$section_adv_gamecredit = 'adv::'.$appname . '::'.$version . '::gamecredit';
		$cachekey_adv_gamecredit = 'adv::'.$appname . '::'.$version . '::gamecredit::' . md5(implode('_',$gids));
		if(CLOSE_CACHE===false && CacheService::section($section_adv_gamecredit)->has($cachekey_adv_gamecredit)){
			$game_credits = CacheService::section($section_adv_gamecredit)->get($cachekey_adv_gamecredit);
		}else{  
		    $game_credits = self::dbClubSlave()->table('game_credit')->whereIn('game_id',$gids)->lists('score','game_id');
		    CLOSE_CACHE===false && CacheService::section($section_adv_gamecredit)->forever($cachekey_adv_gamecredit,$game_credits);
		}						
	    //广告	  	    
	    $advs = array();
	    $cachekey_adv = 'adv::'.$appname . '::'.$version . '::hotgame';
	    if(CLOSE_CACHE===false && CacheService::has($cachekey_adv)){
	    	$_advs = CacheService::get($cachekey_adv);
	    }else{
		    $_advs = self::dbCmsSlave()->table('appadv')
		        ->where('type','=',2)
		        ->where('appname','=',$appname)
		        ->where('version','=',$version)
		        ->orderBy('location','asc')
		        ->get();
		    CLOSE_CACHE===false && CacheService::forever($cachekey_adv,$_advs);
	    }
	    $adv_gids = array();
	    foreach($_advs as $row){
	    	$adv = array();
	    	$adv = self::filterCommonParams($row);
	    	$adv['img'] = self::joinImgUrl($row['litpic']);
	    	$adv['gname'] = $row['advname'];
	    	$adv['desc'] = $row['title'];
	    	$adv['gid'] = $row['gid'];
	    	$advs[$row['location']] = $adv;
	    	if($row['gid']) $adv_gids[] = $row['gid'];
	    }    
	    $adv_games = array();
	    if($adv_gids){
	    	$adv_games = GameService::getGamesByIds($adv_gids);
	    }    	    
		foreach($games as $key=>$row){
			/*
	    	if(in_array($row['id'],$first)){
	    		$games[$key]['isfirst'] = 1;
	    	}else{
	    		$games[$key]['isfirst'] = 0;
	    	}
	    	*/
			
	    	if($key==0&&isset($advs[11])){
	    		$games[$key]['advert'] = $advs[11];
	    	}elseif($key==1&&isset($advs[12])){
	    		$games[$key]['advert'] = $advs[12];
	    	}elseif($key==2&&isset($advs[13])){
	    		$games[$key]['advert'] = $advs[13];
	    	}elseif($key==3&&isset($advs[14])){
	    		$games[$key]['advert'] = $advs[14];
	    	}elseif($key==4&&isset($advs[15])){
	    		$games[$key]['advert'] = $advs[15];
	    	}elseif($key==5&&isset($advs[16])){
	    		$games[$key]['advert'] = $advs[16];
	    	}elseif($key==6&&isset($advs[17])){
	    		$games[$key]['advert'] = $advs[17];
	    	}elseif($key==7&&isset($advs[18])){
	    		$games[$key]['advert'] = $advs[18];
	    	}elseif($key==8&&isset($advs[19])){
	    		$games[$key]['advert'] = $advs[19];
	    	}elseif($key==9&&isset($advs[20])){
	    		$games[$key]['advert'] = $advs[20];
	    	}
	    	
	    }
	    
	    $out = array();
	    //格式化
		foreach($games as $index=>$row){
			$game = array();
			$game['gid'] = $row['id'];
			$game['img'] = GameService::joinImgUrl($row['ico']);
			$game['gname'] = $row['shortgname'];
			$game['free'] = $row['pricetype']==1 ? '1' : '0';
			$game['limitfree'] = $row['pricetype']==2 ? '1' : '0';
			$game['price'] = $row['price'];
			$game['oldprice'] = $row['oldprice'];
			$game['isfirst'] = strval($row['isstarting']);
			$game['moneyCount'] = isset($game_credits[$row['id']]) ? GameService::filterDownloadCredit($row['id'], $uid,0) : 0;
			$game['desc'] = $row['shortcomt'];			
			$game['language'] = GameService::$languages[$row['language']?:0];
			$game['adddate'] = date('Y-m-d H:i:s',$row['addtime']);
			$game['score'] = $row['score'];
									
			if(isset($row['advert']) && is_array($row['advert'])){
				$adv = $row['advert'];				
				if($row['advert']['gid'] && isset($adv_games[$row['advert']['gid']])){
					$adv_game = $adv_games[$row['advert']['gid']];
					$game = array();
					$game['gid'] = $adv_game['id'];
					$game['img'] = GameService::joinImgUrl($adv_game['ico']);
					$game['gname'] = $adv_game['shortgname'];
					$game['free'] = $adv_game['pricetype']==1 ? '1' : '0';
					$game['limitfree'] = $adv_game['pricetype']==2 ? '1' : '0';
					$game['price'] = $adv_game['price'];
					$game['oldprice'] = $adv_game['oldprice'];
					$game['isfirst'] = strval($adv_game['isstarting']);
					$game['desc'] = $adv_game['shortcomt'];		
					$game['language'] = GameService::$languages[$adv_game['language']?:0];	
					$game['adddate'] = date('Y-m-d H:i:s',$adv_game['addtime']);
					$game['score'] = $adv_game['score'];
				}
				$game = array_merge($game,$adv);				
			}
			$out[] = $game;
		} 
		return $out;
	}
	
	/**
	 * 详情页下载按钮广告
	 */
	public static function getGameDownload($gid,$appname,$version)
	{
		$appname = '';
		$cachekey = 'adv::'.$appname . '::'.$version . '::download::btn::' . $gid;
		if(CLOSE_CACHE===false && CacheService::has($cachekey)){
			$adv = CacheService::get($cachekey);
		}else{
			$adv = self::dbCmsSlave()->table('appadv')
				->where('appname','=',$appname)
				->where('version','=',$version)
				->where('type','=',4)
				->where('gid','=',$gid)
				->first();
			CLOSE_CACHE===false && CacheService::forever($cachekey,$adv);
		}
		if(!$adv) return null;
		$out = self::filterCommonParams($adv);
		return $out;
	}
	
	
	/**
	 * 获取资讯详情页广告
	 */
	public static function getDetailAdv($detail_type,$linkid,$game_id=0)
	{
		$data = array();
		if($game_id){
			$game = GameService::getGameInfo($game_id);
		}else{
			switch($detail_type){
				case self::DETAIL_ADV_NEWS:				
				case self::DETAIL_ADV_GUIDE:
				case self::DETAIL_ADV_OPINION:
				case self::DETAIL_ADV_NEWGAME:
					$data = self::dbCmsSlave()->table('article_recommendgame')->where('type','=',$detail_type)->where('linkid','=',$linkid)->first();
					break;
				case self::DETAIL_ADV_TOPIC:
					$data = array('game_id'=>$linkid);
			}
			if($data && isset($data['game_id']) && $data['game_id']>0){
				$game = GameService::getGameInfo($data['game_id']);
				
			}
		}
		
		if(!isset($game) || !$game){
			return null;
		}
		
		$out = array();
		$out['game_id'] = $game['id'];
		$out['game_name'] = $game['shortgname'];
		$out['game_type'] = $game['typename'];
		$out['game_language'] = $game['language'];
		$out['game_size'] = $game['size'];
		$out['game_ico'] = self::joinImgUrl($game['ico']);
		$out['game_downloadurl'] = $game['downurl'];
		return $out;		
	}
	
	/**
	 * 猜你喜欢广告
	 */
	public static function getGuessInfo($appname,$version)
	{
		$appname = '';
		//猜你喜欢推荐位
		$cachekey_pos = 'commend::'.$appname . '::'.$version . '::guesslike';
		if(CLOSE_CACHE===false && CacheService::has($cachekey_pos)){
			$game = CacheService::get($cachekey_pos);
		}else{
			$guess = self::dbCmsSlave()->table('game_recommend')->where('type','=','gl')->orderBy('addtime','desc')->first();
			if($guess){
				$game = GameService::getGameInfo($guess['gid']);
				CLOSE_CACHE===false && CacheService::forever($cachekey_pos,$game);			
			}else{
				$game = null;
			}
		}
		//猜你喜欢广告位
		$cachekey_adv = 'adv::'.$appname . '::'.$version . '::guesslike';
		if(CLOSE_CACHE===false && CacheService::has($cachekey_adv)){
			$adv = CacheService::get($cachekey_adv);
		}else{			
			$adv = self::dbCmsSlave()->table('appadv')
				->where('appname','=',$appname)
				->where('version','=',$version)
				->where('type','=',7)
				->first();						
			CLOSE_CACHE===false && CacheService::forever($cachekey_adv,$adv);
		}
	    
		if($adv && $adv['gid']){
			$game = GameService::getGameInfo($adv['gid']);
			$out = self::filterCommonParams($adv);
		}
		
		
		
		if(!$game){
			return null;
		}				
		$out['gid'] = $game['id'];
		$out['title'] = $game['shortgname'];
		$out['img'] = self::joinImgUrl($game['ico']);
		$out['language'] = $game['language'];
		$out['score'] = $game['score'];
		$out['free'] = $game['pricetype']==1 ? 1 : 0;
		$out['limitfree'] = $game['pricetype'] == 2 ? 1 : 0;
		$out['price'] = $game['price'];
		$out['oldprice'] = $game['oldprice'];
		$out['tname'] = $game['typename'];
		$out['commentcount'] = $game['commenttimes'];
		$count = self::dbClubSlave()->table('account_circle')->where('game_id','=',$adv['gid'])->count();
		$out['viewcount'] = $count;
		$out['downcount'] = $game['downtimes'];
		$out['commcount'] = GameCircleService::getGameCircleUserCount($game['id']);
		$out['postcount'] = GameCircleService::getGameCircleInfoCount($game['id']);
		return $out;
	}
	
	
    /**
	 * 热门推荐的游戏
	 * 
	 */
	public static function getGameCircleHotGame($uid=0,$addtype=0,$is_forum=0)
	{
		//今日推荐
		$cachekey = 'commend::circlerecommend';
		if(CLOSE_CACHE===false && CacheService::has($cachekey)){
			$games = CacheService::get($cachekey);
		}else{
		    $games = self::dbCmsSlave()->table('game_recommend')
	             ->select('games.*')
	             ->where('game_recommend.type','=','c')
	             ->leftJoin('games','game_recommend.gid','=','games.id')
	             ->orderBy('game_recommend.sort','desc')
	             ->get();
	         CLOSE_CACHE===false && CacheService::forever($cachekey,$games);
		}
		//
		$gids = array();
		foreach($games as $row){
			$gids[] = $row['id'];
		}
	    if($is_forum == 1){
			$forum_gids = ForumService::getOpenForumGids();
			$gids = array_intersect($gids,$forum_gids);
		}
		$total = count($gids);
				
		/*    						
	    //广告
	    $advs = array();
	    $_advs = DB::connection(self::$CONN)->table('appadv')
	        ->where('type','=',2)
	        ->orderBy('location','asc')
	        ->get();
	    foreach($_advs as $row){
	    	$advs[$row['location']] = $row;
	    }    
	        
	    //
		foreach($games as $key=>$row){
	    	if(in_array($row['id'],$first)){
	    		$games[$key]['isfirst'] = 1;
	    	}else{
	    		$games[$key]['isfirst'] = 0;
	    	}
	    	if($key==0&&isset($advs[11])){
	    		$games[$key]['advert'] = $advs[11];
	    	}elseif($key==1&&isset($advs[12])){
	    		$games[$key]['advert'] = $advs[12];
	    	}elseif($key==2&&isset($advs[13])){
	    		$games[$key]['advert'] = $advs[13];
	    	}elseif($key==3&&isset($advs[14])){
	    		$games[$key]['advert'] = $advs[14];
	    	}elseif($key==4&&isset($advs[15])){
	    		$games[$key]['advert'] = $advs[15];
	    	}elseif($key==5&&isset($advs[16])){
	    		$games[$key]['advert'] = $advs[16];
	    	}
	    }
	    */
	    $gametype = GameService::getGameTypeOption();
	    if($uid){
			$my_game_ids = GameCircleService::getMyGameIds($uid);		    
		}
		$out = array();
		foreach($games as $index=>$row){
			if(!in_array($row['id'],$gids)) continue;
			$game = array();
			$game['gid'] = $row['id'];
			$game['img'] = GameService::joinImgUrl($row['ico']);
			$game['title'] = $row['gname'];			
			$game['type'] = $gametype[$row['type']];	
			$game['language'] = $row['language'] ? GameService::$languages[$row['language']] : '未知';
			$game['star'] = $row['score'];
			$game['incycle'] = isset($my_game_ids)&& in_array($row['id'],$my_game_ids) ? '1' : '0';
			if(isset($row['advert']) && is_array($row['advert'])){
				$game['advid'] = $row['advert']['id'];
				$game['location'] = $row['advert']['location'];
				$game['advtype'] = '1';
				$game['downurl'] = $row['advert']['downurl'];
				$game['tosafari'] = $row['advert']['tosafari'];
				$game['staturl'] = $row['advert']['url'];
				$game['sendmac'] = $row['advert']['sendmac'];
				$game['sendidfa'] = $row['advert']['sendidfa'];
				$game['sendudid'] = $row['advert']['sendudid'];
				$game['sendos'] = $row['advert']['sendos'];
				$game['sendplat'] = $row['advert']['sendplat'];
				$game['sendactive'] = $row['advert']['sendactive'];
				$game['img'] = AdvService::joinImgUrl($row['advert']['litpic']);
			}
			$out[] = $game;
		} 
		return array('games'=>$out,'total'=>$total);
	}
	
    /**
	 * 广告统计
	 */
	public static function stat($appname,$version,$location,$osversion,$advid,$code,$idfa,$openudid,$type,$linkid)
	{
		$dateline = strtotime(date("Y-m-d"));
		$tb = self::dbCmsMaster()->table('appadv_stat')->where('appname','=',$appname)->where('version','=',$version)->where('location','=',$location)->where('addtime','=',$dateline);
		if($advid){
			$tb = $tb->where('aid','=',$advid);
		}else{
			$tb = $tb->where('type','=',$type)->where('link_id','=',$linkid);
		}
		
		if (version_compare($osversion, '6.0') >= 0){
			$tb = $tb->where('idfa','=',$idfa);
		}else{
			$tb = $tb->where('code','=',$code);
		}
		
		$adv = $tb->first();
		if($adv){
			self::dbCmsMaster()->table('appadv_stat')->where('id','=',$adv['id'])->increment('number');
			return true;
		}else{		
			$data = array();
	    	$data['appname'] = $appname;
	    	$data['version'] = $version;
	    	$data['location'] = $location;
	    	$data['iosversion'] = $osversion;
	    	$data['aid'] = $advid;
	    	$data['code'] = $code;
	    	$data['idfa'] = $idfa;
	    	$data['openudid'] = $openudid;
	    	$data['type'] = $type;
	    	$data['link_id'] = $linkid;
	    	$data['number'] = 1;
    	    $data['addtime'] = $dateline;
    	    self::dbCmsMaster()->table('appadv_stat')->insertGetId($data);
    	    return true;
		}
	}
	
	/**
	 * 广告激活
	 */
	public static function active($code,$idfa,$advid)
	{
		$date = strtotime(date('Y-m-d'));
		if($idfa){
			$count = self::dbCmsSlave()->table('appadv_active_stat')->where('idfa','=',$idfa)->where('aid','=',$advid)->count(); 
			if($count==0){
				$data['aid'] = $advid;
				$data['code'] = $code;
				$data['idfa'] = $idfa;
				$data['addtime'] = $date;
				self::dbCmsMaster()->table('appadv_active_stat')->insertGetId($data);
				return true;
			}
			return null;
		}elseif($code){
			$count = self::dbCmsSlave()->table('appadv_active_stat')->where('code','=',$code)->where('aid','=',$advid)->count(); 
			if($count==0){
				$data['aid'] = $advid;
				$data['code'] = $code;
				$data['idfa'] = $idfa;
				$data['addtime'] = $date;
				self::dbCmsMaster()->table('appadv_active_stat')->insertGetId($data);
				return true;
			}
			return null;
		}
	}	
}