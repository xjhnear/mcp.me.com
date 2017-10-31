<?php
/**
 * @package Youxiduo
 * @category Android 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */

namespace Youxiduo\Android;
use Youxiduo\Android\Model\Activity;

use Illuminate\Support\Facades\Config;
use Youxiduo\Android\Model\Adv;
use Youxiduo\Android\Model\GameApkDownload;
use Youxiduo\Android\Model\GameFirst;
use Youxiduo\Android\Model\GamePlat;
use Youxiduo\Android\Model\GameScore;
use Youxiduo\Android\Model\GameTag;
use Youxiduo\Android\Model\PlatForm;
use Youxiduo\Android\Model\SystemConfig;
use Youxiduo\Android\Model\UserFavorite;
use Youxiduo\Android\Model\Zone;
use Youxiduo\Base\BaseService;
use Youxiduo\Android\Model\AppAdv;
use Youxiduo\Android\Model\Game;
use Youxiduo\Android\Model\GameMustPlay;
use Youxiduo\Android\Model\GameCollect;
use Youxiduo\Android\Model\GameCollectGames;
use Youxiduo\Android\Model\News;
use Youxiduo\Android\Model\Guide;
use Youxiduo\Android\Model\Opinion;
use Youxiduo\Android\Model\Video;
use Youxiduo\Android\Model\Comment;
use Youxiduo\Android\Model\GameVideo;
use Youxiduo\Android\Model\UserGame;
use Youxiduo\Android\Model\GamePackage;
use Youxiduo\Android\Model\GamePackageCollect;
use Youxiduo\Android\Model\GamePackageMatchHistory;
use Youxiduo\Android\Model\UserPackage;
use Youxiduo\Android\Model\Giftbag;
use Youxiduo\Android\Model\GameTool;
use Youxiduo\Android\Model\GameType;
use Youxiduo\Android\Model\Tag;
use Youxiduo\Android\Model\GameRecommend;
use Youxiduo\Android\Model\Recommend;
use Youxiduo\Android\Model\GameDownloadFlow;
use Youxiduo\Android\Model\FollowGame;
use Youxiduo\Helper\Utility;


class GameService extends BaseService
{
	/**
	 * 首页推荐游戏列表
	 */
	public static function getMainGames($appname,$version,$pageIndex=1,$pageSize=15)
	{
		$appadv_list = array();//AppAdv::getList($appname,$version,2,6);
		$adv_count = $appadv_list ? count($appadv_list) : 0;
		$max_count = 100;
		$game_list = Game::getHomeList($max_count-$adv_count);
		
		$datalist = array();
		foreach($appadv_list as $row){
			$data = array();
			$data['title'] = $row['advname'];
			$data['comment'] = $row['title'];
			$data['score'] = 5;
			$data['img'] = Config::get('app.image_url') . $row['litpic'];
			$data['advtype'] = 1;
			$data['downurl'] = $row['downurl'];
			$data['staturl'] = $row['url'];
			$data['location'] = $row['location'];
			$data['advid'] = $row['aid'];
			$data['sendmac'] = $row['sendmac'];
			$data['sendidfa'] = $row['sendidfa'];
			$data['sendudid'] = $row['sendudid'];
			$data['sendos'] = $row['sendos'];
			$data['sendplat'] = $row['sendplat'];
			$data['sendactive'] = $row['sendactive'];
			$data['tosafari'] = $row['tosafari'];
			$datalist[] = $data;
		}
		$gids = array();
		foreach($game_list as $row){
			$gids[] = $row['id'];
		}
		$games_opinion = Opinion::getCountByGameIds($gids); 
		$games_guide   = Guide::getCountByGameIds($gids);
		$games_video   = GameVideo::getCountByGameIds($gids);
		foreach($game_list as $row){
			$data = array();
			$data['gid'] = $row['id'];
			$data['title'] = $row['shortgname'];
			$data['img'] = Config::get('app.image_url') . $row['advpic'];
			$data['icon'] = Config::get('app.image_url') . $row['ico'];
			$data['comment'] = $row['shortcomt'];
			$data['video'] = isset($games_video[$row['id']]) && $games_video[$row['id']]>0 ? true : false;
			$data['free'] = $row['pricetype']==1 ? true : false;
			$data['limitfree'] = $row['pricetype']==2 ? true : false;
			$data['score'] = $row['score'];
			$data['first'] = $row['isstarting'];
			$data['hot'] = $row['ishot'];			
			$data['guide'] = isset($games_guide[$row['id']]) && $games_guide[$row['id']]>0 ? true : false;
			$data['opinion'] = isset($games_opinion[$row['id']]) && $games_opinion[$row['id']]>0 ? true : false;
			$data['linktype'] = $row['linktype'];
			$data['link'] = $row['link'];
			
			$datalist[] = $data;
		}
		
		$pages = array_chunk($datalist,$pageSize);
		$total = count($pages);
		if($pageIndex>$total) $pageIndex = $total;
		return self::trace_result(array('result'=>$pages[$pageIndex-1],'totalCount'=>count($datalist)));
	}
	
	/**
	 * 获取推荐的游戏
	 */
	public static function getRecommendGame($type='ah')
	{
		$out = array();
		if($type=='ah'){
			$red_res = GameRecommend::getList($type,1,8);
			$gids = array();
			foreach($red_res as $row){
				$gids[] = $row['agid'];
			}
			$games = Game::getListByIds($gids);
			foreach($red_res as $row){
				$data = array();
				$data['gid'] = $row['agid'];
				$data['title'] = $games[$row['agid']]['shortgname'];
				$data['img'] = Config::get('app.image_url') . $games[$row['agid']]['ico'];
				$data['downurl'] = '';
				$out[] = $data;
			}
			
		}elseif($type=='g'){
			$red_res = Recommend::getList(1,8);
			
			foreach($red_res as $row){
				$data = array();
				$data['title'] = $row['appname'];
				$data['downurl'] = $row['apkurl'];
				$data['img'] = Config::get('app.image_url').$row['ico'];
				$data['summary'] = $row['summary'];
				$out[] = $data;
			}
		}
		return self::trace_result(array('result'=>$out));
	}
	
	public static function getGameTypeList()
	{
		$result = GameType::getList();
		$types_count = Game::getCountByTypeGroup();
		$out = array();
		foreach($result as $row){
			$data = array();
			$data['gtid'] = $row['id'];
			$data['title'] = $row['typename'];
			$data['img'] = Config::get('app.image_url') . $row['img'] . '?t=20150414';
			$data['gamecount'] = isset($types_count[$row['id']]) ? $types_count[$row['id']] : 0;
			$out[] = $data;
		} 
		return self::trace_result(array('result'=>$out));
	}
	
	public static function getTagListByType($type_id)
	{
		$result = Tag::getListByType($type_id);
		$out = array();
		foreach($result as $row){
			$data = array();
			$data['tag'] = $row['tag'];
			$out[] = $data;
		}
		return self::trace_result(array('result'=>$out));
	}
	
	/**
	 * 经典必玩
	 */
	public static function getMustPlayList($pageIndex,$pageSize)
	{
		$result = GameMustPlay::getList($pageIndex,$pageSize);		
		$total = GameMustPlay::getCount();
		$out = array();
		foreach($result as $row){
			$data = array();
			$data['gid'] = $row['agid'];
			$data['title'] = $row['title'];
			$data['img'] = Config::get('app.image_url') . $row['pic'];
			$out[] = $data;
		}
		return self::trace_result(array('result'=>$out,'totalCount'=>$total));
	}
	
	/**
	 * 特色专题
	 */
	public static function getCollectList($pageIndex,$pageSize)
	{
		$result = GameCollect::getList($pageIndex,$pageSize);
		$total = GameCollect::getCount();
		
		$zt_ids = array();//专题IDs
		foreach($result as $row){
			$zt_ids[] = $row['id'];
		}
		
		$zt_games = GameCollectGames::getListByIds($zt_ids);
		$zt_games_gids = array();//游戏IDs
		foreach($zt_games as $row){
			$zt_games_gids[$row['zt_id']][] = $row['agid'];
		}
		$gids = array();
		foreach($zt_games_gids as $_gids){
			$gids = array_merge($gids,$_gids);
		}
		$games = Game::getListByIds($gids);
		$out = array();
		foreach($result as $row){
			$data = array();
			$data['tid'] = $row['id'];
			$data['title'] = $row['ztitle'];
			$data['img'] = Config::get('app.image_url') . $row['litpic'];
			$data['updatetime'] = date('Y-m-d',$row['addtime']);
			$data['viewcount'] = $row['viewtimes'];
			if(isset($zt_games_gids[$row['id']])){
				foreach($zt_games_gids[$row['id']] as $gid){
					if(!$gid || !isset($games[$gid])) continue;
					$game = $games[$gid];
					$tmp['gid'] = $game['id'];
					$tmp['img'] = Config::get('app.image_url') . $game['ico'];
					$data['games'][] = $tmp;
				}
				$data['gamecount'] = count($zt_games_gids[$row['id']]);
			}else{
				$data['games'] = array();
				$data['gamecount'] = 0;
			}
			$out[] = $data;
		}
		
		return self::trace_result(array('result'=>$out,'totalCount'=>$total));
	}
	
	public static function getGameZhibo($game_id)
	{
		$out = array();
		$out['has_video'] = $game_id==4156 ? true : false;
		$out['video_url'] = $game_id==4156 ? 'http://zhibo.youxiduo.com': '';
		return self::trace_result(array('result'=>$out));
	}
	
	/**
	 * 特色专题详情
	 */
	public static function getCollectDetail($zt_id)
	{
		$zt = GameCollect::getInfoById($zt_id);
		$out = array();
		if($zt){
			//
			$views_num = rand(14,19);
			GameCollect::updateViewTimes($zt_id,$views_num);
			$out['tid'] = $zt['id'];
			$out['title'] = $zt['ztitle'];
			$out['img'] = Config::get('app.image_url') . $zt['litpic'];
			$out['updatetime'] = date('Y-m-d',$zt['addtime']);
			$out['viewcount'] = $zt['viewtimes'];
			$out['desc'] = $zt['description'];		
			$zt_games = GameCollectGames::getListById($zt_id);
			$gids = array();
			foreach($zt_games as $row){
				$gids[] = $row['agid'];
			}
		    $games = Game::getListByIds($gids);
		    
		    $games_opinion = Opinion::getCountByGameIds($gids); 
		    $games_guide   = Guide::getCountByGameIds($gids);
		    $games_video   = GameVideo::getCountByGameIds($gids);
		    $games_comment = Comment::getCountByGameIds($gids);
		    foreach($gids as $gid){
				if(!$gid || !isset($games[$gid])) continue;
				$game = array();
				$game['gid'] = $games[$gid]['id'];
				$game['img'] = Config::get('app.image_url') . $games[$gid]['ico'];
				$game['title'] = $games[$gid]['shortgname'];				
				$game['free'] = $games[$gid]['pricetype']==1 ? true : false;
				$game['limitfree'] = $games[$gid]['pricetype']==2 ? true : false;
				$game['summary'] = $games[$gid]['editorcomt'];
				$game['size'] = $games[$gid]['size'];
				$game['score'] = $games[$gid]['score'];
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
			
			return self::trace_result(array('result'=>$out));
		}else{
			return self::trace_error('E1','专题不存在');
		}
	}
	
	/**
	 * 是否显示悬浮按钮
	 */
	public static function isExistsButton($packagename)
	{
		$pkg_list = array($packagename);
		$pkg_res = GamePackage::getGameListByPackage($pkg_list);
		$out = array('gameid'=>0,'article'=>false,'giftbag'=>false,'activity'=>false,'tools'=>false);
		if($pkg_res){
			$game_id = $pkg_res[0]['gid'];
			$gids = array($game_id);
			$games_news    = News::getCountByGameIds($gids);
			$games_opinion = Opinion::getCountByGameIds($gids); 
			$games_guide   = Guide::getCountByGameIds($gids);
			$games_video   = GameVideo::getCountByGameIds($gids);
			$games_giftbag = Giftbag::getCountByGameIds($gids,true);
			$games_tool    = GameTool::getCountByGameIds($gids);
			$games_activity = Activity::getCountByGameIds($gids);
			
			$news_flag = isset($games_news[$game_id]) && $games_news[$game_id]>0 ? true : false;
			$guide_flag = isset($games_guide[$game_id]) && $games_guide[$game_id]>0 ? true : false;
			$opinion_flag = isset($games_opinion[$game_id]) && $games_opinion[$game_id]>0 ? true : false;
			$video_flag = isset($games_video[$game_id]) && $games_video[$game_id]>0 ? true : false;
			$activity_flag = isset($games_activity[$game_id]) && $games_activity[$game_id]>0 ? true : false;
					
			$out['gameid'] = $game_id;
			$out['article'] = ($news_flag||$guide_flag||$opinion_flag||$video_flag) ? true : false;
			$out['giftbag'] = isset($games_giftbag[$game_id]) && $games_giftbag[$game_id]>0 ? true : false;
			$out['activity'] = $activity_flag;
			$out['tools'] = isset($games_tool[$game_id]) ? true : false;
		}
		return self::trace_result(array('result'=>$out));
	}
	
	/**
	 * 
	 */
	public static function getGameTools($game_id)
	{
		if($game_id){
			$gids = array($game_id);
		}else{
			return self::trace_error('E1','参数错误');
		}
		$games_tool    = GameTool::getCountByGameIds($gids);
		$out = array();
		if(isset($games_tool[$game_id])){
			$out['tool_id'] = $games_tool[$game_id];
		}else{
			$out = array();
		}
		
		return self::trace_result(array('result'=>$out));
	}
	
	public static function matchGames($uid,array $gname_list=array(),$gids=array())
	{
	    if($uid){
			$res = UserGame::getGameIdsByUid($uid);
			foreach($res as $row){
				$gids[] = $row['game_id'];
			}
		}
		$match_gids = Game::getGidsByGnames($gname_list);
		
		if($match_gids){
			$uid > 0 && UserGame::addMyGame($uid,$match_gids);
		}
		
		$gids = array_merge($gids,$match_gids);
	    $games = Game::getListByIds($gids);
		
		$games_news    = News::getCountByGameIds($gids);
		$games_opinion = Opinion::getCountByGameIds($gids); 
		$games_guide   = Guide::getCountByGameIds($gids);
		$games_video   = GameVideo::getCountByGameIds($gids);
		$games_giftbag = Giftbag::getCountByGameIds($gids,true);
		$games_tool    = GameTool::getCountByGameIds($gids);
		$games_activity = Activity::getCountByGameIds($gids);
		
		$out = array();
		foreach($games as $game){
			$data = array();
			$data['gid'] = $game['id'];//游戏ID
			$data['gname'] = $game['shortgname'];//游戏名称
			$data['count'] = $game['downtimes'];//下载数
			$data['ico'] = Config::get('app.image_url') . $game['ico'];
			$news_flag = isset($games_news[$game['id']]) && $games_news[$game['id']]>0 ? true : false;
			$guide_flag = isset($games_guide[$game['id']]) && $games_guide[$game['id']]>0 ? true : false;
			$opinion_flag = isset($games_opinion[$game['id']]) && $games_opinion[$game['id']]>0 ? true : false;
			$video_flag = isset($games_video[$game['id']]) && $games_video[$game['id']]>0 ? true : false;
			
			$data['has_info'] = ($news_flag||$guide_flag||$opinion_flag||$video_flag) ? true : false;
			$data['has_gift'] = isset($games_giftbag[$game['id']]) && $games_giftbag[$game['id']]>0 ? true : false;
			$data['has_tool'] = isset($games_tool[$game['id']]) && $games_tool[$game['id']]>0 ? true : false;
			$data['has_activity'] = isset($games_activity[$game['id']]) && $games_activity[$game['id']]>0 ? true : false;
			$data['toolid'] = isset($games_tool[$game['id']]) ? $games_tool[$game['id']] : array();
			
			$out[] = $data;
		}
		return self::trace_result(array('result'=>$out));
	}
	
	/**
	 * 我的游戏
	 */
	public static function getMyGameHome($uid,array $pkg_list = array(),$gids=array(),$idcode='')
	{		
		if($uid){
			$res = UserGame::getGameIdsByUid($uid);
			foreach($res as $row){
				$gids[] = $row['game_id'];
			}
		}
		$pkgname_list = array();
		$pkgname_list_out = array();
		$add_gids = array();
		if($pkg_list){
			$pkg_res = GamePackage::getGameListByPackage($pkg_list);
			GamePackageCollect::updateMatchCount($pkg_list,$idcode);
			foreach($pkg_res as $row){
				$gid = $row['gid'];
				//$gid && $gids[] = $gid; 
				$gid && $add_gids[] = $gid;
				$pkgname_list[$row['gid']] = $row['apk_package_name'];
				$pkgname_list_out[$row['gid']][] = $row['apk_package_name'];
			} 
			//自动添加匹配的游戏到我的游戏
			$uid > 0 && UserGame::addMyGame($uid,$add_gids);
		}
		
		if($gids){
			$pkg_res = GamePackage::getGameListByGameId($gids);
		    foreach($pkg_res as $row){
				$pkgname_list[$row['gid']] = $row['apk_package_name'];
				$row['apk_package_name'] && $pkgname_list_out[$row['gid']][] = $row['apk_package_name'];
			}
		}		
		$gids = array_merge($gids,$add_gids);

		$games = Game::getListByIds($gids);
		
		$games_news    = News::getCountByGameIds($gids);
		$games_opinion = Opinion::getCountByGameIds($gids); 
		$games_guide   = Guide::getCountByGameIds($gids);
		$games_video   = GameVideo::getCountByGameIds($gids);
		$games_giftbag = Giftbag::getCountByGameIds($gids,true);
		$games_tool    = GameTool::getCountByGameIds($gids);
		$games_activity = Activity::getCountByGameIds($gids);
		
		$out = array();
		foreach($games as $game){
			$data = array();
			$data['gid'] = $game['id'];//游戏ID
			$data['gname'] = $game['shortgname'];//游戏名称
			$data['count'] = $game['downtimes'];//下载数
			$data['packagename'] = isset($pkgname_list_out[$game['id']]) ? array_values(array_unique($pkgname_list_out[$game['id']])) : array();
			$data['ico'] = Config::get('app.image_url') . $game['ico'];
			$news_flag = isset($games_news[$game['id']]) && $games_news[$game['id']]>0 ? true : false;
			$guide_flag = isset($games_guide[$game['id']]) && $games_guide[$game['id']]>0 ? true : false;
			$opinion_flag = isset($games_opinion[$game['id']]) && $games_opinion[$game['id']]>0 ? true : false;
			$video_flag = isset($games_video[$game['id']]) && $games_video[$game['id']]>0 ? true : false;
			
			$data['has_info'] = ($news_flag||$guide_flag||$opinion_flag||$video_flag) ? true : false;
			$data['has_gift'] = isset($games_giftbag[$game['id']]) && $games_giftbag[$game['id']]>0 ? true : false;
			$data['has_tool'] = isset($games_tool[$game['id']]) && $games_tool[$game['id']]>0 ? true : false;
			$data['has_activity'] = isset($games_activity[$game['id']]) && $games_activity[$game['id']]>0 ? true : false;
			$data['toolid'] = isset($games_tool[$game['id']]) ? $games_tool[$game['id']] : array();
			
			$out[] = $data;
		}
		
		$not_storage = array_values(array_diff($pkg_list,$pkgname_list));
		if($not_storage){
			$pkg_res = GamePackageCollect::getGameListByPackage($not_storage);
			
			$wait_pkg = array();						
			foreach($pkg_res as $row){
				$data = array();
				$data['gid'] = 0;
				$data['gname'] = $row['APP_NAME'];
				$data['count'] = $row['MATCH_COUNT'];
				$data['packagename'] = array($row[strtoupper('apk_packagename')]);
				$data['ico'] = $row[strtoupper('app_icon')];
				$data['has_info'] = false;
				$data['has_gift'] = false;
				$data['has_tool'] = false;
				$data['toolid'] = array();
				$out[] = $data;
				$wait_pkg[] = $row[strtoupper('apk_packagename')];
			}
			//保存匹配到但未入库的游戏
			UserPackage::saveMatchData($wait_pkg);
			//保存匹配记录
			$uid>0 && GamePackageMatchHistory::saveMatchHistory($uid, $wait_pkg);
		}
		
		return self::trace_result(array('result'=>$out));
	}
	
	/**
	 * 添加我的游戏
	 * @param int $uid
	 * @param array $gids
	 */
	public static function addMyGame($uid,$gids)
	{
		if(!$uid || !$gids) return self::trace_error('参数不能为空');
		UserGame::addMyGame($uid,$gids);
		return self::trace_result(array('result'=>true));
	}
	
    /**
	 * 删除我的游戏
	 * @param int $uid
	 * @param array $gids
	 */
	public static function removeMyGame($uid,$gids)
	{
		if(!$uid || !$gids) return self::trace_error('E1','参数不能为空');
		UserGame::removeMyGame($uid,$gids);
		return self::trace_result(array('result'=>true));
	}

    /**
     * 获取分类下的游戏列表
     */
    public static function getGamesList($pageIndex,$pageSize,$gametype,$order,$pricetype,$sort,$tag,$week)
    {
        $order_field = 'id';
        switch($order){
        	case 1:
        		$order_field = 'id';
        		break;
        	case 2:
        		$order_field = 'weekdown';
        		break;
        	case 3:
        		$order_field = 'score';
        		break;
        	default:
        		$order_field = 'id';
        		break;
        }
        $order_sort = 'desc';
        switch($sort){
        	case 1:
        		$order_sort = 'asc';
        		break;
        	case 2:
        		$order_sort = 'desc';
        		break;
        	default:
        		$order_sort = 'desc';
        		break;
        }

        $out = array();
        $count = 0;
        if(!empty($tag)){
            $res = Game::getGamesByTag($tag,$pageIndex,$pageSize,$order_field,$order_sort,$pricetype,$gametype);
        }else{
            $res = Game::getGames($order_field,$order_sort,$pageIndex,$pageSize,null,$gametype,$pricetype);
        }
        $out = Game::_exportGamesRes($res['rs'],$week);
        $count = $res['count'];

        return self::trace_result(array('result'=>$out,'totalCount'=>$count));
    }

    /**
     * 获取游戏详情头部信息
     */
    public static function getGameShowHeader($appname,$version,$gid,$uid)
    {
        $out = array();
        $games = Game::getListByIds(array($gid));
        
        $game = ($games && isset($games[$gid]) && $games[$gid]) ? $games[$gid] : null;
        if($game){
            $advtype =Config::get('yxd.adv.GAME_DETAIL_DOWN_ADV');
            $gameAdv = AppAdv::getDetailByGid($appname,$version,$gid,$advtype);
            if ($gameAdv) {
                $out['advtype'] = 1;
                $out['downurl'] = $gameAdv['downurl'];
                $out['staturl'] = $gameAdv['url'];
                $out['location'] = $gameAdv['location'];
                $out['advid'] = $gameAdv['aid'];
                $out['sendmac'] = $gameAdv['sendmac'];
                $out['sendidfa'] = $gameAdv['sendidfa'];
                $out['sendudid'] = $gameAdv['sendudid'];
                $out['sendos'] = $gameAdv['sendos'];
                $out['sendplat'] = $gameAdv['sendplat'];
                $out['sendactive'] = $gameAdv['sendactive'];
                $out['tosafari'] = $gameAdv['tosafari'];
            }
        }else{
            return self::trace_error('E1','游戏不存在');
        }

        //游戏专题
        $zone = Zone::getDetailByGid($gid);
        if($zone){
            $out['zqtitle'] = $zone['title'];
            $out['zqurl'] = $zone['linkurl'];
        }

        $out['have_downplat'] = $game['isup'] ? GamePlat::_checkHaveApkDownPlat($gid) : false;
        $out['gid'] = $game['id'];
        $out['title'] = trim($game['shortgname']);
        $out['img'] = Utility::getImageUrl($game['ico']);
        $out['score'] = $game['score'];
        
        if ($game['pricetype'] == '1'){
            $out['free'] = true;
            $out['limitfree'] = false;
        }
        if ($game['pricetype'] == '2'){
            $out['free'] = false;
            $out['limitfree'] = true;
        }
        if ($game['pricetype'] == '3'){
            $out['free'] = false;
            $out['limitfree'] = false;
        }

        $out['price'] = isset($game['price']) && $game['price'] ? $game['price'] : '0.0';
        $out['oldprice'] = isset($game['oldprice']) && $game['oldprice'] ? $game['oldprice'] : '0.0';
        $out['viewscore'] = $game['viewscore'];
        $out['funnyscore'] = $game['funnyscore'];
        $out['smoothscore'] = $game['smoothscore'];
        $out['size'] = $game['size'];
        $out['version'] = $game['version'];
        $out['download'] =	$game['isup'] ? GamePlat::_getOneGameDownurl($game['id']) : '';
        $gtype = GameType::getInfoById($game['type']);
        $out['gametype'] = isset($gtype['typename']) ? $gtype['typename'] : '';

        $tag_rs = GameTag::getDetailByGid($gid);
        foreach ($tag_rs as $k => $v){
            $out['tags'][$k]['tag'] = $v['tag'];
        }

        $out['updatetime'] = date("Y-m-d", $game['addtime']);
        $out['developer'] = $game['company'];
        $out['zone'] = 1;

        $out['downcount'] = $game['downtimes'];
        $comments = Comment::getCountByGameIds(array($gid));
        $out['commentcount'] =  $comments && isset($comments[$gid]) ? $comments[$gid] : 0 ;
        $out['tname'] = $out['gametype'];
        $out['tab'] = 1;
        $advgame = Adv::getInfoByGid($gid);
        $out['tab'] = $advgame && isset($advgame['tab']) ? $advgame['tab'] : 1;
        $out['favs'] = 0;
        if($uid){
            $favs = UserFavorite::getUserFavorite($uid,1,1,$gid);
            $out['favs'] = $favs && $favs[0] ? 1 : 0;
        }

        //多平台
        $packageName_num = GamePackage::getGameListByGameId(array($gid));
        //单平台
        $packageName_one =GamePackage::getGameOneByGameId(array($gid));
        $platlist = array();
        if($packageName_num){
            foreach ($packageName_num as $k => $v){
                $platlist[$k]['packagename'] = (string)$v['apk_package_name'];
                $platlist[$k]['platcount'] = $v['apk_download_num'] ? $v['apk_download_num'] : 0;
                $platlist[$k]['platid'] = $v['apk_platform'] ? $v['apk_platform'] : 0;
            }
        }

        $out['platinfo'] = $platlist;
        $out['packagename'] = $packageName_one && $packageName_one['apk_package_name'] ? $packageName_one['apk_package_name'] : '';

        return self::trace_result(array('result'=>$out));
    }

    /**
     * 游戏详情
     */
    public static function getGameShow($gid,$uid)
    {
        $out = array();
        $game = Game::getListByIds(array($gid));
        if(!isset($game[$gid])) return self::trace_error('E10');
        $game = $game[$gid];

        $images = array();	//游戏图片
        $images = explode(",", $game['pics']);
        $pic = array();
        $vertical = false;
        $picpath= Config::get('app.game_icon_path');
        if ($images){
            foreach ($images as $k => $v){
                $pic[$k]['img'] = Utility::getImageUrl($v);
            }
            /*
            $imagesize = getimagesize($picpath . $images[0]);
            if ($imagesize[0] < $imagesize[1]){
                $vertical = true;
            }
            */
        }
        $out['gid'] = $game['id'];
        $out['title'] = $game['shortgname'];
        $out['img'] = Utility::getImageUrl($game['ico']);
        $out['score'] = $game['score'];
        $out['size'] = $game['size'];
        $out['version'] = $game['version'];
        $out['download'] = $game['isup'] ? GamePlat::_getOneGameDownurl($gid) : '';
        $out['appraise'] = $game['editorcomt'];

        $out['video'] = false;
        $tmp = GameVideo::getCountByGameIds(array($gid));
        if($tmp && isset($tmp[$gid]) && $tmp[$gid] > 0){
            $out['video'] = true;
        }
        
        $out['price'] = isset($game['price']) ? $game['price'] : '0.0';
        $gtype = GameType::getInfoById($game['type']);
        $out['gametype'] = $gtype ? $gtype['typename'] : '';

        $out['tags'] = "";
        $tag_rs = GameTag::getDetailByGid($gid);
        if ($tag_rs){
            $tags = '';
            foreach ($tag_rs as $k => $v){
                $tags .= $v['tag'].",";
            }
            $tags = substr($tags, 0, -1);
            $out['tags'] = $tags;
        }
        
        $out['language'] = Game::getGameLanguageName($game['language']);
        $out['updatetime'] = date("Y-m-d", $game['addtime']);
        $out['developer'] = $game['company'];
        $out['platform'] = $game['platform'];
        $out['images'] = $pic;
        $out['vertical'] = $vertical;
        $out['likes'] = Game::guessYouLike($game['type']);
        $out['zone'] = $game['zonetype'];

        if($uid){
            $gsRow = GameScore::getScoreByUid($uid,$gid);
            $score = $gsRow ? $gsRow['score'] : '';
            $counts = GameScore::getCountByGameIds(array($gid));
            $out['score'] = array('score'=>$score, 'count'=>$counts && isset($counts[$gid]) ? (int)$counts[$gid] : 0);
        }

        $out['devgames'] = Game::getDevOtherGames($game['company']);

        return self::trace_result(array('result'=>$out));
    }

    /**
     * 关键词搜索
     */
    public static function searchKeyWord($keyword,$pageIndex=1,$pageSize=10)
    {
        $keyword = trim($keyword);
        
        $out = array();
        if($keyword){
            $rs = Game::search($keyword,$pageIndex,$pageSize);
            $type_list = $type_arrs = array();
            $type_list = GameType::getList();
            if($type_list){
                foreach ($type_list as $row){
                    $type_arrs[$row['id']] = $row;
                }
            }
            
            if ($rs){
                foreach ($rs as $k => $v){
                    $out[$k]['gid'] = $v['id'];
                    $out[$k]['title'] = $v['shortgname'];
                    $out[$k]['img'] = Utility::getImageUrl($v['ico']);
                    $out[$k]['score'] = $v['score'];
                    $out[$k]['size'] = $v['size'];
                    $out[$k]['price'] = isset($v['price']) ? $v['price'] : '0.0';
                    $out[$k]['oldprice'] = isset($v['oldprice']) ? $v['oldprice'] : '0.0';
                    if ($v['pricetype'] == '1'){
                        $out[$k]['free'] = true;
                        $out[$k]['limitfree'] = false;
                    }
                    if ($v['pricetype'] == '2'){
                        $out[$k]['free'] = false;
                        $out[$k]['limitfree'] = true;
                    }
                    if ($v['pricetype'] == '3'){
                        $out[$k]['free'] = false;
                        $out[$k]['limitfree'] = false;
                    }
                    $out[$k]['tname'] = isset($type_arrs[$v['type']]) ? $type_arrs[$v['type']]['typename'] : '';
                    $out[$k]['downcount'] = $v['downtimes'];//$v['isup'] ? down_count($v['downtimes'], $v['downrand']) : 0;
                    $out[$k]['commentcount'] = ($counts = Comment::getCountByGameIds(array($v['id']))) && isset($counts[$v['id']]) ? $counts[$v['id']] : 0;
                }
            }            
        }
        return self::trace_result(array('result'=>$out));
    }

    /**
     * 游戏平台下载列表
     */
    public static function getGamePlats($gid)
    {
        $games = Game::getListByIds(array($gid));
        if(!isset($games[$gid])) return self::trace_error('E10');
        $game = $games[$gid];
        $out = array();
        $plat_apk = array();

        if($game['isup']==1){
            $plat_apk = GamePlat::getPlatListByGameId($gid);
        }
        $pid_arr = array();
        if($plat_apk){
            foreach ($plat_apk as $i => $res){
                if($res['pid']){
                    $pid_arr[] = $res['pid'];
                }
            }
        }

        $pid_arr = array_unique($pid_arr);
        $plat_arr = array();
        if($pid_arr){
            $plats = PlatForm::getListByIds($pid_arr);
            if($plats){
                foreach ($plats as $rs){
                    $plat_arr[$rs['id']] = $rs;
                }
            }
        }

        $apkresult = GamePackage::getGameListByGameId(array($gid));
        if($apkresult){
            foreach($apkresult as $v){
                $packagename[$v['id']] = $v['apk_package_name'];
            }
        }

        if($plat_apk){
            foreach ($plat_apk as $k => $v){
                $out[$k]['pid'] = $v['pid'];
                $out[$k]['gname'] = $game['shortgname'];
                $out[$k]['pname'] = $plat_arr[$v['pid']]['platname'];
                $out[$k]['psize']  = $v['psize'] ? $v['psize'] : $game['size'];
                $out[$k]['pversion'] = $v['pversion'] ? $v['pversion'] : $game['version'];
                $out[$k]['downurl'] = $v['downurl'];
                $out[$k]['istop'] = $v['istop'];
                $out[$k]['packagename'] = isset($packagename[$v['id']]) ? $packagename[$v['id']] : '';
            }
        }

        $res_desc = SystemConfig::getDownloadPlatDesc();
        $plat_desc = !empty($res_desc) ? $game['shortgname'].$res_desc['value'] : '';
        $data = array('platdesc'=>$plat_desc,'iconurl'=>Utility::getImageUrl($game['ico']),'platlist' => $out);;
        return self::trace_result(array('result'=>$data));
    }

    /**
     * 开测表
     */
    public static function getGameFirst($type,$pageIndex,$pageSize)
    {
        $out = array();
        $list = array();
        $count = 0;
        $count_ht = 0;		//热门和今日的数量
        $page_total = 0;
        $page_total_ht = 0;	//热门和今日的页数

        $cur_date = date('Y-m-d',time());
        $cur_datetime = strtotime($cur_date);
        $future_week = strtotime("+1 week"); //未来一周
        $history_week = strtotime("-1 week");  //过去一周

        if($type == 1){
            //先查询总数(热门或今日 未来一周 过去一周)
            $count = GameFirst::getInWeekCount($cur_datetime,$future_week,$history_week);
            //再查询热门和今日开测的总数
            $count_ht = GameFirst::getCurrentDayHotCount($cur_datetime);

            //最后一页热门的数量
            $last_pht_count = ($count_ht + $pageSize) % $pageSize;
            $page_total_ht = ($count_ht > 0) ?  ceil($count_ht/$pageSize) : 0;	//热门和今日的页数
            if($pageIndex <= $page_total_ht){
                //先取热门及今日开测的数据
                $list_ht = GameFirst::getCurrentDayHot($cur_datetime,$pageIndex,$pageSize);
                if(($pageIndex == $page_total_ht) && ($last_pht_count > 0) && ($last_pht_count <= $pageSize)){
                    $pageSize = $pageSize - $last_pht_count;
                    $pageIndex = 1;
                    $list_rs = GameFirst::getInWeekNotHot($cur_datetime,$future_week,$history_week,$pageIndex,$pageSize);
                }

                if(!empty($list_ht) && !empty($list_rs)){
                    $list = array_merge($list_ht,$list_rs);
                }elseif (!empty($list_ht) && empty($list_rs)){
                    $list = $list_ht;
                }elseif (empty($list_ht) && !empty($list_rs)){
                    $list = $list_rs;
                }
            }else{
                $_page = ($pageIndex - $page_total_ht);
//                if($last_pht_count > 0){
//                    $offset = $pageSize*($_page - 1) + ($pageSize - $last_pht_count);	//起始条数
//                }else{
//                    $offset = $pageSize*($_page - 1);
//                }
                $list = GameFirst::getInWeekNotHot($cur_datetime,$future_week,$history_week,$_page,$pageSize);
            }
        }elseif ($type == 2){
            $wheres = array(array('addtime','>',$cur_datetime),array('istop','!=',1));
        }elseif ($type == 3){
            $wheres = array(array('addtime','<',$cur_datetime),array('istop','!=',1));
        }else {
            $wheres = array();
        }

        if($type != 1){
            $res = GameFirst::currentDayBeforeOrAfter($pageIndex,$pageSize,$wheres);
            $list = $res['result'];
            $count = $res['count'];
        }

        $agid_arr  = array_unique(array_pluck($list,'agid'));
        $type_arrs = array();
        $type_list = GameType::getList();
        if($type_list){
            foreach ($type_list as $row){
                $type_arrs[$row['id']] = $row;
            }
        }
        $tag_rs = array();
        $tags = array();
        $gtags_arr = array();
        if($agid_arr){
            $tag_rs = GameTag::getListByGids($agid_arr);
            if($tag_rs){
                foreach ($tag_rs as $row){
                    $tags[$row['agid']][] = $row['tag'];
                }
                if(!empty($tags)){
                    foreach ($tags as $i => $res){
                        if(count($res) > 2){//每个游戏最多取两个标签
                            $tags[$i] = array_slice($res, 0, 2);
                        }
                        $gtags_arr[$i] = (!empty($tags[$i]) && is_array($tags[$i])) ? implode(' ', $tags[$i]) : '';
                    }
                }
            }
        }

        $out = $future = array();
        if ($list){
            $currentTime = time();
            foreach ($list as $k=>$v){
                if($currentTime >= $v['addtime']){
                    $out[$k]['gid']	=	$v['agid'];
                    $out[$k]['state']	=	$v['state'];
                    $out[$k]['title']	=	$v['shortgname'];
                    $out[$k]['istop']	=	$v['istop'];
                    $out[$k]['isfirst']	=	$v['isfirst'];
                    $out[$k]['gametype']=	isset($type_arrs[$v['type']]) ? $type_arrs[$v['type']]['typename'] : '';
                    $out[$k]['adddate']	=	date('Y-m-d', $v['addtime']);
                    $out[$k]['pic']		=	Config::get('app.image_url').$v['ico'];
                    $out[$k]['tag']		=	isset($gtags_arr[$v['agid']]) ? $gtags_arr[$v['agid']] : '';
                }else if($currentTime < $v['addtime']){
                    $future[$k]['gid']	=	$v['agid'];
                    $future[$k]['state']	=	$v['state'];
                    $future[$k]['title']	=	$v['shortgname'];
                    $future[$k]['istop']	=	$v['istop'];
                    $future[$k]['isfirst']	=	$v['isfirst'];
                    $future[$k]['gametype']=	isset($type_arrs[$v['type']]) ? $type_arrs[$v['type']]['typename'] : '';
                    $future[$k]['adddate']	=	date('Y-m-d', $v['addtime']);
                    $future[$k]['pic']		=	Config::get('app.img_url').$v['ico'];
                    $future[$k]['tag']		=	isset($gtags_arr[$v['agid']]) ? $gtags_arr[$v['agid']] : '';
                }
            }
        }

        if($future){
            usort ( $future ,  function($key='adddate'){
                function ( $a ,  $b ) use ( $key ) {
                    return  strnatcmp ( $a [ $key ],  $b [ $key ]);
                };
            });
        }
        if(is_array($out) && is_array($future)){
            $out = array_merge_recursive($out,$future);
        }

        return self::trace_result(array('result' => $out,'totalCount' => $count));


    }

    /**
     * 随机的游戏分类标签
     */
    public static function getRandGameTag()
    {
        $tags = Config::get('yxd.discovery_tags');
        $tag_tmp = array_rand($tags, 7);
        $out = array();
        foreach ($tag_tmp as $k => $v){
            $out[$k]['tag'] = $tags[$v]['tag'];
            $out[$k]['name'] = $tags[$v]['name'];
        }
        return self::trace_result(array('result'=>$out));
    }
    
    public static function addFollow($uid,$game_id)
    {
    	$exists = FollowGame::db()->where('uid','=',$uid)->where('game_id','=',$game_id)->first();
    	$result = true;
    	if(!$exists){
    		$data = array('uid'=>$uid,'game_id'=>$game_id,'ctime'=>time());
    		$result = FollowGame::db()->insertGetId($data) ? true : false;
    	}
    	return self::trace_result(array('result' =>$result));
    }
    
    public static function removeFollow($uid,$game_id)
    {
    	$exists = FollowGame::db()->where('uid','=',$uid)->where('game_id','=',$game_id)->delete();
    	$result = $exists ? true : false;
    	return self::trace_result(array('result' =>$result));
    }
    
    public static function isFollow($uid,$game_id)
    {
    	$exists = FollowGame::db()->where('uid','=',$uid)->where('game_id','=',$game_id)->first();
    	$result = $exists ? true : false;
    	return self::trace_result(array('result' =>$result));
    }

    /**
     * 游戏下载统计
     * @param int $gid
     * @param int $pid
     * @param int $status
     */
    public static function gameDownloadCount($gid,$pid,$status,$uid,$idcode)
    {
        if($gid){
            //$num = rand(3, 10);
            //Game::downloadCount($gid,$num);
            //GamePlat::downloadCount($gid,$pid,$num);
            //GameApkDownload::downloadCount($gid,$pid);
            $data = array(
                'gid'=>$gid,
                'pid'=>$pid,
                'status'=>$status,
                'uid'=>$uid,
                'idcode'=>$idcode,
                'ctime'=>time(),
                'is_sync'=>0
            );
            $success = GameDownloadFlow::db()->insert($data);
            if(!$success){
            	GameDownloadFlow::db()->insert($data);
            }
            return self::trace_result();
        }
        return self::trace_error('E50');
    }
    
    public static function doDownloadWork()
    {
    	
    }

}

