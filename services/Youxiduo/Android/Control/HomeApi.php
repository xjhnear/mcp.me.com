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

namespace Youxiduo\Android\Control;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Youxiduo\Base\BaseService;
use Youxiduo\Android\Model\Game;
use Youxiduo\Helper\Utility;
use Youxiduo\Android\Model\GamePlat;
use Youxiduo\Android\Model\Task;
use Youxiduo\Android\Model\TaskAccount;
use Youxiduo\Android\Model\Checkinfo;
use Youxiduo\Android\Model\ActivityTask;
use Youxiduo\Android\Model\ActivityTaskUser;
use Youxiduo\Android\Model\Giftbag;
use Youxiduo\Android\Model\Activity;
use Youxiduo\Android\Model\GiftbagAccount;
use Youxiduo\Android\Model\GiftbagCard;
use Youxiduo\Android\Model\Reserve;

use Youxiduo\Android\AdvService;

/**
 * 首页封装服务
 */
class HomeApi extends BaseService
{
	public static function home()
	{
		$uid = (int)Input::get('uid');
		$cachekey = 'app:home:'.$uid;
		/*
		if(Cache::has($cachekey)){
			return Cache::get($cachekey);
		}	
		*/	
		$out = array();
		//推荐游戏
		$out['recommend_game'] = (object)array();
		//游戏列表
		$out['today_game_list'] = array();
		//广告条
		$out['banner'] = array();
		//推荐任务
		$out['recommend_task'] = array();
		//日常任务
		$out['daily_task'] = array();
		//猜你喜欢
		$out['guess_like'] = array(
		    'activity_list'=>array(),
		    'giftbag_list'=>array()
		);
		
		//今天推荐
        $out['recommend_game'] = self::getRecommendGameOut();
		//游戏列表
		$out['today_game_list'] = self::getGameListOut();
		//广告banner
		$out['banner'] = self::getBannerOut();
		//推荐任务
		$out['recommend_task'] = self::getRecommendTaskOut();
		//日常任务
	    $out['daily_task'] = self::getDailyTaskOut($uid);
		//猜你喜欢
		$gids_str = strval(Input::get('gids',''));
        $gids = !empty($gids_str) ? (strpos($gids_str,',')!==false ? explode(',',$gids_str): array($gids_str)) : array();
        $out['guess_like']['recommend_list'] = self::getGuessRecommendOut();
        $out['guess_like']['activity_list'] = self::getGuessActivityOut($gids,$uid);
		$out['guess_like']['giftbag_list'] = self::getGuessGiftbagOut($gids,$uid); 
		//Cache::put($cachekey,$out,30);
		return $out;
	}
	protected static function getRecommendGameOut()
	{
		$out = array();
		$game = Game::db()->where('is_app_hot_top','=',1)->first();
		if($game){
			$out['gid'] = $game['id'];
			$out['title'] = $game['shortgname'];
			$out['img'] = Config::get('app.image_url') . $game['app_adv_img'];
			$out['icon'] = Config::get('app.image_url') . $game['ico'];
			$out['comment'] = $game['shortcomt'];
			$out['free'] = $game['pricetype']==1 ? true : false;
			$out['limitfree'] = $game['pricetype']==2 ? true : false;
			$out['score'] = $game['score'];
			$out['first'] = $game['isstarting'];
			$out['hot'] = $game['ishot'];			
			$out['linktype'] = $game['linktype'];
			$out['link'] = $game['link'];
		}
		return $out;
	}
	
	protected static function getGameListOut()
	{
		$game_list = Game::getHomeList(4);	
	    $gids = array();
		foreach($game_list as $row){
			$gids[] = $row['id'];
		}
		$out = array();
		foreach($game_list as $row){
			$data = array();
			$data['gid'] = $row['id'];
			$data['title'] = $row['shortgname'];
			$data['img'] = Config::get('app.image_url') . $row['advpic'];
			$data['icon'] = Config::get('app.image_url') . $row['ico'];
			$data['comment'] = $row['shortcomt'];
			$data['free'] = $row['pricetype']==1 ? true : false;
			$data['limitfree'] = $row['pricetype']==2 ? true : false;
			$data['score'] = $row['score'];
			$data['first'] = $row['isstarting'];
			$data['hot'] = $row['ishot'];			
			$data['linktype'] = $row['linktype'] ? : '';
			$data['link'] = $row['link'] ? : '';
			
			$out[] = $data;
		}
		return $out;	
	}
	
	protected static function getBannerOut($appname='',$channel='',$version='')
	{
		$out = array();
		$advs = AdvService::getRecommendPlaceList($appname,$channel,$version,'12',10);
		if(!$advs) return $out;
		foreach($advs as $adv){
			$tmp['img'] = Utility::getImageUrl($adv['img']);
			$tmp['linktype'] = $adv['linktype'];
			$tmp['link'] = $adv['link'];
			$out[] = $tmp;
		}
		return $out;
	}
	
	protected static function getRecommendTaskOut()
	{
		$out = array();
		$out[] = array('img'=>Utility::getImageUrl('/userdirs/common/android/home_share.jpg?v='.date('Ymd')),'type'=>2);//分享
		$out[] = array('img'=>Utility::getImageUrl('/userdirs/common/android/home_running.jpg?v='.date('Ymd')),'type'=>1);//试玩		
		$out[] = array('img'=>Utility::getImageUrl('/userdirs/common/android/home_recharge.jpg?v='.date('Ymd')),'type'=>3);//代充
		return $out;
	}
	
	protected static function getDailyTaskOut($uid)
	{
	    $time = time();
		$search = array('relation_task_id'=>0);		
		$search['start_time'] = $time;
		$search['end_time'] = $time;
		$search['is_top'] = 1;
		$search['is_show'] = 1;
		$order = array('sort'=>'desc','id'=>'desc');
	    if($uid){
			$all_user_tasks = ActivityTaskUser::searchTaskStatus(array('uid'=>$uid));
			$all_user_atids = array_keys($all_user_tasks);
		}else{
			$all_user_tasks = array();
			$all_user_atids = array();
		}
	    $child_task_ids = TaskApi::getChildTaskIds($all_user_atids,null);
		$all_task_ids = ActivityTask::buildSearch($search)->lists('id');
		if($child_task_ids && is_array($child_task_ids) && !empty($child_task_ids)){
			$all_task_ids = array_merge($all_task_ids,$child_task_ids);
		}
		$user_search = array('in_ids'=>$all_task_ids,'is_top'=>1);
		$result = ActivityTask::searchList($user_search,1,9,$order);
		$total = ActivityTask::searchCount($user_search);						
		$gids = array();
		foreach($result as $row){
			$gids[] = $row['gid'];
		}
		$out = array();
		$games = Game::getListByIds($gids);
		foreach($result as $row){
			if(!isset($games[$row['gid']])) continue;
			$tmp = array();
			$tmp['aid'] = $row['id'];
			$tmp['title'] = $row['title'];
			$tmp['gname'] = $games[$row['gid']]['shortgname'];
			$tmp['img'] = Utility::getImageUrl($games[$row['gid']]['ico']);
			$tmp['game_package_name'] = $row['game_package_name'];
			$tmp['reward_type'] = $row['reward_type'];
			$tmp['giftbag_id'] = $row['giftbag_id'];
			$tmp['goods_id'] = $row['goods_id'];
			$tmp['money'] = $row['money'];
			$tmp['complete_type'] = $row['complete_type'];
			$tmp['complete_status'] = ($all_user_atids && in_array($row['id'],$all_user_atids)) ? $all_user_tasks[$row['id']] : 0;
			$out[] = $tmp;
		}
		return $out;
	}
	
	protected static function getGuessRecommendOut($appname='',$channel='',$version='')
	{
		$out = array();
		$advs = AdvService::getRecommendPlaceList($appname,$channel,$version,'13',10);
		if(!$advs) return $out;
		foreach($advs as $adv){
			$tmp['title'] = $adv['title'];
			$tmp['comment'] = $adv['words'];
			$tmp['icon'] = Utility::getImageUrl($adv['img']);
			$tmp['linktype'] = $adv['linktype'];
			$tmp['link'] = $adv['link'];
			$out[] = $tmp;
		}
		return $out;
	}
	
	protected static function getGuessActivityOut($gids,$uid)
	{
	    //活动
	    //$total = Activity::getTotalCountByGameIds($gids);
		$result = Activity::getListByGameIds(1,6,$gids);
		$gids = array();
		foreach($result as $row){
			$gids[] = $row['agid'];
		}
		$games = Game::getListByIds(array_unique($gids));
		$out = array();
		foreach($result as $row){
			$data = array();
			$gid = $row['agid'];
			$data['atid'] = $row['id'];
			$data['title'] = $row['title'];
			$data['url'] = Config::get('app.image_url') . (empty($row['pic'])&&isset($games[$gid]) ? $games[$gid]['ico'] : $row['pic']);
			$data['gname'] = empty($row['gname'])&&isset($games[$gid]) ? $games[$gid]['shortgname'] : $row['gname'];
			$data['type'] = $row['type'];
			$data['adddate'] = $row['adddate'];
			$data['ishot'] = $row['ishot'] ? true : false;
			$data['istop'] = $row['istop'] ? true : false;
			$data['starttime'] = date('Y-m-d',$row['starttime']);
			$data['endtime'] = date('Y-m-d',$row['endtime']);
			$data['starttimenew'] = date('Y-m-d H:i:s',$row['starttime']);
			$data['endtimenew'] = date('Y-m-d H:i:s',$row['endtime']);
			$data['redirect_type'] = $row['redirect_type'];
			$data['linktype'] = $row['linktype'];
			$data['link'] = $row['link'];
			
			$out[] = $data;
		}
		return $out;
	}
	
    protected static function getGuessGiftbagOut($gids,$uid)
	{
	    //礼包
        //$total = Giftbag::getTotalCountByGameIds($gids,true);
        $result = array();
        if($gids){
		    $result = Giftbag::getListByGameIds($gids,1,12,true);
        }
		if(!$result){
			$result = Giftbag::getList(0,1,12,true);
		}
		//数量检查
		$real_num = count($result);
		if($real_num<12){
			$append_num = 12 - $real_num;
			$append_result = Giftbag::getList(0,2,$append_num,true);
			$result = array_merge($result,$append_result);
		}
		
		$gids = array();
		foreach($result as $row){
			$gids[] = $row['game_id'];
		}
		
		$games = Game::getListByIds($gids);
		$out = array();
		foreach($result as $row){
			$data = array();
			$gid = $row['game_id'];
			$data['gfid'] = $row['id'];			
			$data['title'] = $row['title'];
			$data['adddate'] = date('Y-m-d',$row['ctime']);
			$data['date'] =date('Y-m-d',$row['ctime']);
			$data['starttime'] = date('Y-m-d H:i:s',$row['starttime']);
			$data['endtime'] = $row['endtime'] ? date('Y-m-d H:i:s',$row['endtime']) : date('Y-m-d',time()).' 23:59:59';
			$data['url'] = Config::get('app.image_url') . (empty($row['listpic'])&&isset($games[$gid]) ? $games[$gid]['ico'] : $row['listpic']);
			$data['gname'] = empty($row['gname'])&&isset($games[$gid]) ? $games[$gid]['shortgname'] : '';
			$data['ishot'] = $row['is_hot'] ? true : false;
			$data['istop'] = $row['is_top'] ? true : false;
			$data['cardcount'] = $row['total_num'];
			$data['lastcount'] = $row['last_num'];
			$data['ishas'] = false;
			$data['number'] = '';
			if($uid){
				Reserve::db()->where('uid','=',$uid)->where('gift_id','=',$row['id'])->update(array('gift_id'=>0));
				$card = GiftbagAccount::getMyGiftbagInfo($row['id'],$uid);
				if($card){
					$data['ishas'] = true;
					$data['number'] = $card['card_no'];
				}
			}
			$out[] = $data;
		}
		return $out;
	}
}