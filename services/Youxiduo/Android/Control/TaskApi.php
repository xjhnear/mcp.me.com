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

use Youxiduo\Android\Model\CheckinsTaskUser;

use Youxiduo\Android\Model\CheckinsTask;
use Yxd\Modules\System\SettingService;
use Youxiduo\Helper\Utility;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Youxiduo\Base\BaseService;

use Youxiduo\Android\Model\Game;
use Youxiduo\Android\Model\GamePlat;
use Youxiduo\Android\Model\Task;
use Youxiduo\Android\Model\TaskAccount;
use Youxiduo\Android\Model\Checkinfo;
use Youxiduo\Android\Model\ActivityShareHistory;
use Youxiduo\Android\Model\ActivityTask;
use Youxiduo\Android\Model\ActivityTaskLimit;
use Youxiduo\Android\Model\ActivityTaskUser;
use Youxiduo\Android\Model\ActivityTaskUserScreenshot;
use Youxiduo\V4\User\MoneyService;
use Youxiduo\V4\Common\ShareService;
use Youxiduo\Android\BaiduPushService;
use Youxiduo\Android\Model\UserDevice;

use Youxiduo\V4\Activity\Model\ChannelClick;
use Youxiduo\V4\Activity\Model\DownloadChannel;
use Youxiduo\V4\Activity\Model\StatisticConfig;
use Youxiduo\Android\TaskService;

/**
 * 任务封装服务
 */
class TaskApi extends BaseService
{
	public static function perfectTaskList()
	{
		$uid = Input::get('uid');
		$out = array();
		$result = Task::db()->where('type','=',2)->get();
		$my_task_ids = array();
		if($uid){
		    $my_task_ids = TaskAccount::db()->where('uid','=',$uid)->where('task_type','=',2)->lists('task_id');
		}
		foreach($result as $row){
			$row['condition'] = json_decode($row['condition'],true);
			$row['reward'] = json_decode($row['reward'],true);
			if($row['condition']['closed']==1) continue;
			$tmp = array();
			$tmp['action'] = $row['action'];
			$tmp['name'] = $row['step_name'];
			$tmp['money'] = $row['reward']['score'];
			$tmp['finish_status'] = ($my_task_ids && in_array($row['id'],$my_task_ids)) ? true : false;
			$out[] = $tmp;
		}
		
		return $out;
	}
	
	public static function checkinsHistory()
	{
		$uid = Input::get('uid');
		$start_time = Input::get('start_time');
		$end_time   = Input::get('end_time');
		$tb = Checkinfo::db()->where('uid','=',$uid);
		if($start_time){		
			$tb = $tb->where('ctime','>=',strtotime($start_time));
		}
		
	    if($end_time){		
			$tb = $tb->where('ctime','<=',strtotime($end_time . ' 23:59:59'));
		}
		$result = $tb->get();
		$out = array();
		foreach($result as $row){
			$out[] = date('Y-m-d',$row['ctime']);
		}
		return $out;
	}
	
	/**
	 * 签到奖励任务
	 */
	public static function checkinsReward()
	{
		$uid = Input::get('uid');
		$start_time = Input::get('start_time');
		$end_time   = Input::get('end_time');
		$search = array('start_time'=>strtotime($start_time),'end_time'=>strtotime($end_time),'is_show'=>1);
		$tasks = CheckinsTask::searchList($search,1,4,array('sort'=>'desc','id'=>'desc'));
		$res = CheckinsTaskUser::searchList(array('uid'=>$uid),1,31,array('id'=>'desc'));
		$task_ids = array();
		foreach($res as $row){
			$task_ids[] = $row['ctid'];
		}
		$out = array();
		$out['tasks'] = array();
		foreach($tasks as $row){
			$tmp = array();
			$tmp['title'] = $row['title'];
			$tmp['img'] = Utility::getImageUrl($row['reward_img']);
			$tmp['money'] = $row['reward_value'];
			$tmp['complete_status'] = ($task_ids && in_array($row['id'],$task_ids)) ? 1 : 0;
			$out['tasks'][] = $tmp;
		}
		$out['running_days'] = count(Checkinfo::getContinuousCheckin($uid,date('j')));//连续
		$out['cumulative_days'] = (int)CheckInfo::getCurrentMonthCheckinsTimes($uid);//累计
		$config = SettingService::getConfig('android_checkins_agreement');
		if($config){
			$out['content'] = $config['data']['content'];
		}else{		
		    $out['content'] = '';
		}
		return $out;
	}
	
	public static function activityTaskListBgImg()
	{
		$type = (int)Input::get('type');
		$out = '';
		if($type==1){
			$out = Utility::getImageUrl('/userdirs/common/android/running-bg.jpg');
		}elseif($type==2){
			$out = Utility::getImageUrl('/userdirs/common/android/share-bg.jpg');
		}elseif($type==3){
			$out = Utility::getImageUrl('/userdirs/common/android/recharge-bg.jpg');
		}
		return $out;
	}
	
	public static function activityTaskList()
	{
		$uid = Input::get('uid');
		$type = (int)Input::get('type');
		$pageIndex = Input::get('pageIndex',1);
		$pageSize = Input::get('pageSize',10);
		
		$out = array();
			
		if($uid){
			$all_user_tasks = ActivityTaskUser::searchTaskStatus(array('uid'=>$uid));
			$all_user_atids = array_keys($all_user_tasks);
		}else{
			$all_user_tasks = array();
			$all_user_atids = array();
		}
		
		$time = time();
		$search = array('relation_task_id'=>0);
		if($type){
		    $search['action_type'] = $type;
		}
		$search['start_time'] = $time;
		$search['end_time'] = $time;
		$search['is_show'] = 1;
	    $child_task_ids = self::getChildTaskIds($all_user_atids,null);
		$all_task_ids = ActivityTask::buildSearch($search)->lists('id');
		if($child_task_ids && is_array($child_task_ids) && !empty($child_task_ids)){
			$all_task_ids = array_merge($all_task_ids,$child_task_ids);
		}
		
		$user_search = array('in_ids'=>$all_task_ids);
		$result = ActivityTask::searchList($user_search,$pageIndex,$pageSize,array('sort'=>'desc','id'=>'desc'));
		$total = ActivityTask::searchCount($user_search);
		
		$gids = array();
		foreach($result as $row){
			$gids[] = $row['gid'];
		}
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
		return array('result'=>$out,'totalCount'=>$total);
	}
	
	public static function activityTaskDetail()
	{
		$aid = Input::get('aid');
		$uid = Input::get('uid',0);
		$idcode = Input::get('idcode');
		$search['id'] = $aid;
		$out = array();
		$info = ActivityTask::findOne($search);
		if(!$info) return (object)null;
		$game = Game::db()->where('id','=',$info['gid'])->first();
		if(!$game) return (object)null;
		$downinfo = GamePlat::db()->where('agid','=',$game['id'])->first();
		$days = 0;
		if($info['end_time']>time()){
			$days = ceil(($info['end_time']-time())/(3600*24));
		}
		
		$out['aid'] = $aid;
		$out['title'] = $info['title'];
		$out['gname'] = $game['shortgname'];
		$out['img'] = Utility::getImageUrl($game['ico']);
		$out['gid'] = $game['id'];
		$out['downurl'] = isset($downinfo['downurl']) ? $downinfo['downurl'] : '';
		$out['game_package_name'] = $info['game_package_name'];
		$out['money'] = $info['money'];
		$out['content'] = Utility::formatContent($info['content'],false);
		$out['last_days'] = $days;
		$out['complete_type'] = $info['complete_type'] ? : '';//download|running|screenshot
		$complete_condition = $info['complete_condition'];
		if($info['action_type']==1){//试玩
			if($info['complete_type']=='download'){
				//$out['download_status'] = '';
			}if($info['complete_type']=='running'){
				$out['total_time'] = $complete_condition['total_time'];
			}if($info['complete_type']=='screenshot'){
				
			}
		}elseif($info['action_type']==2){//分享
			$out['complete_type'] = 'share';
			$out['share_title'] = $complete_condition['share_title'];
			$out['share_icon'] = Utility::getImageUrl($complete_condition['share_icon']);
			$out['share_content'] = $complete_condition['share_weixin'];
			$out['share_url'] = self::makeShareUrl($complete_condition['share_redirect_url'],$aid,$uid,$info['title']);
			
		}elseif($info['action_type']==3){//充值
			$out['complete_type'] = 'recharge';
			foreach($complete_condition as $key=>$val){
			    $out['form_fields'][$key] = $val;
			}
			$out['form_fields']['gid_hidden'] = 'recharge_' . $aid;
		}
		if($uid){
			$user_task = ActivityTaskUser::findOne(array('atid'=>$aid,'uid'=>$uid));
			if($user_task){
				$out['complete_status'] = $user_task['complete_status'];
				$out['reward_status'] = $user_task['reward_status'];
				
			}else{
				$out['complete_status'] = 0;
				$out['reward_status'] = 0;
			}
		}else{
		    $out['complete_status'] = 0;
		    $out['reward_status'] = 0;
		}
		/*
		$exists = ActivityTaskLimit::isLimitedDevice($aid,$idcode,$uid);
		if($exists==true){
			$out['complete_status'] = 1;
		}
		*/
		return $out;
	}
	
	protected static function makeShareUrl($redirect_url,$task_id,$uid,$title)
	{
	    $channel_id = 'channel_sharetask_' . $task_id;
		$channel_name = $title;
		$config_id = 'config_sharetask_' . $task_id . '_' . $uid;
		$config_name = $title;
		$callback_url = 'http://android.api.youxiduo.com/android/click_callback' . '?aid=' . $task_id . '&uid=' . $uid;
		$redirect_url = ShareService::makeMonitorUrl($channel_id,$channel_name,$config_id,$config_name,$redirect_url,$callback_url);
		return ShareService::getShortUrl($redirect_url);
	}		
	
	public static function userTaskList()
	{
		$uid = Input::get('uid');
		$pageIndex = Input::get('pageIndex',1);
		$pageSize = Input::get('pageSize',10);
		
		$out = array();	
		//完善资料	
		$out['result']['perfect_userinfo'] = array('money'=>0);				
		$perfect_money = 0;
	    $all_tasks = Task::db()->where('type','=',2)->get();
		$my_task_ids = array();
		if($uid){
		    $my_task_ids = TaskAccount::db()->where('uid','=',$uid)->where('task_type','=',2)->lists('task_id');
		}
		foreach($all_tasks as $row){
			$row['condition'] = json_decode($row['condition'],true);
			$row['reward'] = json_decode($row['reward'],true);
			if($row['condition']['closed']==1) continue;						
			$completed = ($my_task_ids && in_array($row['id'],$my_task_ids)) ? true : false;
			if($completed) continue;
			$perfect_money += $row['reward']['score'];
		}		
		$out['result']['perfect_userinfo'] = array('money'=>$perfect_money);
		//用户已完成的任务
		$all_user_tasks = ActivityTaskUser::searchList(array('uid'=>$uid),1,10000);		
		$all_user_atids = array();
		$all_user_task_status = array();
		$all_user_task_kv = array();
		foreach($all_user_tasks as $row){
			$all_user_atids[] = $row['atid'];
			$all_user_task_status[$row['atid']] = $row['complete_status'];
			$all_user_task_kv[$row['atid']] = $row;
		}
		$all_user_atids = array_unique($all_user_atids);
		
		//待领奖
		$out['result']['wait_reward_list'] = array();
		$today = time();//mktime(0,0,0,date('m'),date('d'),date('Y'));
		$gids = array();
		$wait_atids = ActivityTaskUser::searchTaskIds(array('uid'=>$uid,'complete_status'=>2));	
	    $wait_result = array();
		$wait_search['is_show'] = 1;
		if($wait_atids){
			$wait_search['in_ids'] = $wait_atids;
			$wait_result = ActivityTask::searchList($wait_search,1,50);
		}else{
			$wait_result = array();
		}
		//待领奖
	    foreach($wait_result as $row){
			$gids[] = $row['gid'];
		}
		
		//
		$games = Game::getListByIds($gids);
		//待领奖
	    foreach($wait_result as $row){
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
			$tmp['start_time'] = date('Y-m-d H:i:s',$row['start_time']);
			$tmp['end_time'] = date('Y-m-d H:i:s',$row['end_time']);
			$tmp['complete_status'] = ($all_user_atids && in_array($row['id'],$all_user_atids)) ? $all_user_task_status[$row['id']] : 0;
			$out['result']['wait_reward_list'][] = $tmp;
		}
		$out['result']['task_list'] = self::getTaskDayOut($wait_atids,$all_user_atids,$all_user_task_kv);
		$out['hasMore'] = false;
		return $out;
	}
	
	protected static function getTaskDayOut($wait_atids,$all_user_atids,$all_user_task_kv)
	{
		$search = array('is_show'=>1,'relation_task_id'=>0);
		if($wait_atids){
		    $search['not_in_ids'] = $wait_atids;
		}
		$child_task_ids = self::getChildTaskIds($all_user_atids,$all_user_task_kv);
		$all_task_ids = ActivityTask::buildSearch($search)->lists('id');
		if($child_task_ids && is_array($child_task_ids) && !empty($child_task_ids)){
			$all_task_ids = array_merge($all_task_ids,$child_task_ids);
		}
		
		$result = ActivityTask::searchList(array('in_ids'=>$all_task_ids),1,3000,array('sort'=>'desc','id'=>'desc'));
		$min_date = ActivityTask::db()->where('is_show','=',1)->min('start_time');
		$min_date = strtotime(date('Y-m-d',$min_date));
		//$max_date = ActivityTask::db()->where('is_show','=',1)->min('end_time');
		//$max_date = strtotime(date('Y-m-d',$max_date));		
		$today = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$days = (($today-$min_date)/86400)+1;
		if($days<0) return array();
		
		$gids = array();
		foreach($result as $row){
			$gids[] = $row['gid'];
		}
		
		$games = Game::getListByIds($gids);
		
		$all_result = array();
		for($date=$today;$date>=$min_date;$date-=86400){
			foreach($result as $row){
				$start_date = strtotime(date('Y-m-d',$row['start_time']));
				$end_date = strtotime(date('Y-m-d',$row['end_time']));
				if($start_date <= $date && $date <= $end_date){
					unset($row['content']);
					$all_result[$date][] = $row;
				}				
			}
		}
		ksort($all_result,SORT_NUMERIC);
		$finish_tasks = array();
		foreach($all_result as $date=>$res){
			foreach($res as $key=>$row){
				if(in_array($row['id'],$finish_tasks)){
					unset($res[$key]);
					continue;
				}
				if($all_user_atids && in_array($row['id'],$all_user_atids)){
					$complete_date = strtotime(date('Y-m-d',$all_user_task_kv[$row['id']]['complete_time']));
					if($complete_date==$date){
						$row['complete_status'] = $all_user_task_kv[$row['id']]['complete_status'];
						$finish_tasks[] = $row['id'];						
					}else{
						$row['complete_status'] = $all_user_task_kv[$row['id']]['complete_status'];
					}
				}else{
					$row['complete_status'] = 0;
				}
				$res[$key] = $row;
			}
			$all_result[$date] = $res;
		}	
		krsort($all_result);
		$out = array();
		//$child_tasks = self::getChildTask($all_user_atids,$all_user_task_kv);
		foreach($all_result as $date=>$res){
			$data = array();
			foreach($res as $key=>$row){
				if(!isset($games[$row['gid']])) continue;				
				$data[] = self::formatTask($row,$games);
				/*
				if($row['is_relation_task']==1 && $row['complete_status']==1){
					if(isset($child_tasks[$row['id']])){
						$data[] = self::formatTask($child_tasks[$row['id']],$games);
						$second = $child_tasks[$row['id']];
						if($second['is_relation_task']==1 && $second['complete_status']==1){
							if(isset($child_tasks[$second['id']])){
								$data[] = self::formatTask($child_tasks[$second['id']],$games);
							}
						}
					}
				}
				*/
			}
			if($data){
				$out[] = array(date('Y-m-d',$date),$data);
			}
		}
		return $out;
	}
	
	public static function formatTask($row,$games)
	{
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
		$tmp['start_time'] = date('Y-m-d H:i:s',$row['start_time']);
		$tmp['end_time'] = date('Y-m-d H:i:s',$row['end_time']);
		$tmp['complete_status'] = $row['complete_status'];
		return $tmp;
	}
	
	public static function getChildTaskIds($all_user_atids,$all_user_task_kv)
	{
		if(empty($all_user_atids)) return array();
	    $search = array('is_show'=>1,'is_relation_task'=>1,'child_task'=>1);
		$time = time();		
		$search['start_time'] = $time;
		$search['end_time'] = $time;
		$search['in_relation_task_id'] = $all_user_atids;
		$ids = ActivityTask::buildSearch($search,1,100)->lists('id');
		return $ids;		
	}
	
	public static function getChildTask($all_user_atids,$all_user_task_kv)
	{
		$search = array('is_show'=>1,'is_relation_task'=>1,'child_task'=>1);
		$time = time();		
		$search['start_time'] = $time;
		$search['end_time'] = $time;
		$_child_tasks = ActivityTask::buildSearch($search,1,100)->get();
		$child_tasks = array();
		
		$gids = array();
		foreach($_child_tasks as $row){
			$gids[] = $row['gid'];
		}
		
		$games = Game::getListByIds($gids);
	    foreach($_child_tasks as $row){
	    	if($all_user_atids && in_array($row['id'],$all_user_atids)){
	    		$row['complete_status'] = $all_user_task_kv[$row['id']]['complete_status'];
	    	}else{
	    		$row['complete_status'] = 0;
	    	}
			$child_tasks[$row['relation_task_id']] = $row;
		}
		
		return $child_tasks;
	}
	
	/**
	 * 任务中心
	 */
	public static function _userTaskList()
	{
		$uid = Input::get('uid');
		$type = (int)Input::get('type');
		$pageIndex = Input::get('pageIndex',1);
		$pageSize = Input::get('pageSize',10);
				
		$out = array();		
		$out['result']['perfect_userinfo'] = array('money'=>0);
		$out['result']['current_list'] = array();
		$out['result']['wait_reward_list'] = array();
		$out['result']['ended_list'] = array();
		$out['totalCount'] = 0;
		
		//完善资料
		$perfect_money = 0;
	    $all_tasks = Task::db()->where('type','=',2)->get();
		$my_task_ids = array();
		if($uid){
		    $my_task_ids = TaskAccount::db()->where('uid','=',$uid)->where('task_type','=',2)->lists('task_id');
		}
		foreach($all_tasks as $row){
			$row['condition'] = json_decode($row['condition'],true);
			$row['reward'] = json_decode($row['reward'],true);
			if($row['condition']['closed']==1) continue;						
			$completed = ($my_task_ids && in_array($row['id'],$my_task_ids)) ? true : false;
			if($completed) continue;
			$perfect_money += $row['reward']['score'];
		}
		
		$out['result']['perfect_userinfo'] = array('money'=>$perfect_money);
		
		
		$today = time();//mktime(0,0,0,date('m'),date('d'),date('Y'));
		$wait_atids = ActivityTaskUser::searchTaskIds(array('uid'=>$uid,'complete_status'=>2));		
		$all_user_tasks = ActivityTaskUser::searchTaskStatus(array('uid'=>$uid));
		$all_user_atids = array_keys($all_user_tasks);
		
		
		//进行中
		$current_result = array();
		$current_search['is_show'] = 1;
		$current_search['start_time'] = $today;
		$current_search['end_time'] = $today;
		$current_search['not_in_ids'] = $all_user_atids;
		$current_result = ActivityTask::searchList($current_search,1,50);
		//待领奖
		$wait_result = array();
		$wait_search['is_show'] = 1;
		if($wait_atids){
			$wait_search['in_ids'] = $wait_atids;
			$wait_result = ActivityTask::searchList($wait_search,1,50);
		}else{
			$wait_result = array();
		}
		//已结束
		$end_search = array();
		$end_search['is_show'] = 1;		
		//$end_search['ended'] = $today;
		$end_search['uid'] = $uid;
		//$end_search['not_in_atid'] = $wait_atids;								
		$end_atids = array();
		$user_all_end_tasks = ActivityTaskUser::searchList(array('uid'=>$uid,'not_in_atid'=>$wait_atids),$pageIndex,$pageSize,array('complete_time'=>'desc'));
		foreach($user_all_end_tasks as $row){
			$end_atids[] = $row['atid'];
		}
		$end_search['in_ids'] = $end_atids;
		
		$out['totalCount'] = ActivityTask::searchCount($end_search);				
		$ended_result = ActivityTask::searchList($end_search,$pageIndex,$pageSize);
	    $gids = array();
		foreach($current_result as $row){
			$gids[] = $row['gid'];
		}
		
	    foreach($wait_result as $row){
			$gids[] = $row['gid'];
		}
		
	    foreach($ended_result as $row){
			$gids[] = $row['gid'];
		}
				
		$games = Game::getListByIds($gids);
		foreach($current_result as $row){
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
			$tmp['start_time'] = date('Y-m-d H:i:s',$row['start_time']);
			$tmp['end_time'] = date('Y-m-d H:i:s',$row['end_time']);
			$tmp['complete_status'] = ($all_user_atids && in_array($row['id'],$all_user_atids)) ? $all_user_tasks[$row['id']] : 0;
			$out['result']['current_list'][] = $tmp;
		}
		
	    foreach($wait_result as $row){
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
			$tmp['start_time'] = date('Y-m-d H:i:s',$row['start_time']);
			$tmp['end_time'] = date('Y-m-d H:i:s',$row['end_time']);
			$tmp['complete_status'] = ($all_user_atids && in_array($row['id'],$all_user_atids)) ? $all_user_tasks[$row['id']] : 0;
			$out['result']['wait_reward_list'][] = $tmp;
		}
		
		$a = array();
		foreach($ended_result as $row){
			$a[$row['id']] = $row;
		}
		
	    foreach($user_all_end_tasks as $one){
	    	if(!isset($a[$one['atid']])) continue;
	    	$row = $a[$one['atid']];
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
			$tmp['start_time'] = date('Y-m-d H:i:s',$row['start_time']);
			$tmp['end_time'] = date('Y-m-d H:i:s',$row['end_time']);
			$tmp['complete_status'] = ($all_user_atids && in_array($row['id'],$all_user_atids)) ? $all_user_tasks[$row['id']] : 0;
			$tmp['complete_time'] = date('Y-m-d H:i:s',$one['complete_time']); 
			$out['result']['ended_list'][] = $tmp;
		}
		
		return $out;
	}	
	
	/**
	 * 
	 */
	public static function updateProgress()
	{
		$gid = Input::get('gid');
		$aid = Input::get('aid');
		$game_package_name = Input::get('game_package_name');
		$uid = Input::get('uid');
		$download_status = Input::get('download_status');//
		$total_time = (int)Input::get('total_time');
        //验证是否在黑名单
        $idcode  = Input::get('idcode');
        if(isset($idcode)&&!empty($idcode)){
            if(self::check_blacklist_by_idcode($idcode)){
                return "已加入黑名单";
            }
        }
		if($uid==-1) return '尚未登录,不能完成任务';


		$time = time();
		$search['id'] = $aid;
		$search['start_time'] = $time;
		$search['end_time'] = $time;
		$search['is_show'] = 1;		
		$task = ActivityTask::findOne($search);		
		if(!$task) return '任务不存在';
		$exists = ActivityTaskLimit::isLimitedDevice($aid,$idcode,$uid);
		if($exists === true) {
			$title = $task['title'].'任务完成';
			$message = '贪心的小朋友,每个手机只能做一次'.$task['title'].'任务哟';
			self::sendTaskMessage($uid,$title,$message,23);
			return $message;
		}
		$is_limit = ActivityTaskLimit::addLimitedDevice($aid,$idcode,$uid);
		if($is_limit !== true){
			$title = $task['title'].'任务完成';
			$message = '贪心的小朋友,每个手机只能做一次'.$task['title'].'任务哟';
			self::sendTaskMessage($uid,$title,$message,23);
			return $message;
		}
		$user_task = ActivityTaskUser::findOne(array('atid'=>$aid,'uid'=>$uid));
		if($task['action_type']==1){//试玩
			if($task['complete_type']=='download'){//下载
				if($user_task) return '该任务已经做过';
				$data = array('atid'=>$aid,'uid'=>$uid,'complete_status'=>1,'reward_status'=>0,'complete_time'=>time());
				$success = ActivityTaskUser::saveAddOrUpdate($data);				
				//游币奖励
				if($success){
					//$is_limit = ActivityTaskLimit::addLimitedDevice($aid,$idcode,$uid);
				    //if($is_limit === false) return '您已经完成过该任务了,同一台手机设备只奖励一次！';
					$money = $task['money'];
					$info = '任务奖励:'.$task['title'].'';
					$reward_success = MoneyService::doAccount($uid,$money,'reward',$info);					
					if($reward_success) {
						ActivityTaskUser::updateStatus($success,array('reward_status'=>1,'reward_time'=>time()));
						$title = $task['title'].'任务完成';
				    	$message = '完成'.$task['title'].'任务奖励'.$money.'游币';
				    	self::sendTaskMessage($uid,$title,$message,23);
					}
				}
				$success_message = '任务完成,您获得'.$money.'游币';
				return $success ? $success_message : '任务失败';
			}if($task['complete_type']=='running'){//试玩
				if($user_task) return '该任务已经做过';
				$data = array('atid'=>$aid,'uid'=>$uid,'complete_status'=>1,'reward_status'=>0,'complete_time'=>time(),'process'=>$total_time);
				$success = ActivityTaskUser::saveAddOrUpdate($data);
				//游币奖励
				if($success){
					//$is_limit = ActivityTaskLimit::addLimitedDevice($aid,$idcode,$uid);
				    //if($is_limit === false) return '您已经完成过该任务了,同一台手机设备只奖励一次！';
					$money = $task['money'];
					$info = '任务奖励:'.$task['title'].'';
					$reward_success = MoneyService::doAccount($uid,$money,'reward',$info);					
					if($reward_success) {
						ActivityTaskUser::updateStatus($success,array('reward_status'=>1,'reward_time'=>time()));
						$title = $task['title'].'任务完成';
				    	$message = '完成'.$task['title'].'任务奖励'.$money.'游币';
				    	self::sendTaskMessage($uid,$title,$message,23);					
					}
				}
				$success_message = '任务完成,您获得'.$money.'游币';
				return $success ? $success_message : '任务失败';
			}if($task['complete_type']=='screenshot'){//截屏
				if($user_task) return '该任务已经做过';
				return '任务无效';			
			}
		}elseif($task['action_type']==2){//分享
			if($user_task) return '该任务已经做过';			
			//$is_limit = ActivityTaskLimit::addLimitedDevice($aid,$idcode,$uid);
		    //if($is_limit === false) return '您已经完成过该任务了,同一台手机设备只奖励一次！';
			$data = array('atid'=>$aid,'uid'=>$uid,'complete_status'=>2,'reward_status'=>0,'complete_time'=>time());
			$success = ActivityTaskUser::saveAddOrUpdate($data);
			return $success ? '分享成功，满足获奖条件后奖品将自动发放。' : '任务失败';
			/*
			//游币奖励
			if($success){
				$money = $task['money'];
				$info = '任务奖励:'.$task['title'].'';
				$reward_success = MoneyService::doAccount($uid,$money,'reward',$info);					
			    if($reward_success) {
			    	ActivityTaskUser::updateStatus($success,array('reward_status'=>1,'reward_time'=>time()));
			    	$title = $task['title'].'任务完成';
			    	$message = '完成'.$task['title'].'任务奖励'.$money.'游币';
			    	self::sendTaskMessage($uid,$title,$message,23);
			    }
			}
			$success_message = '任务完成,您获得'.$money.'游币';
			return $success ? $success_message : '任务失败';
			*/
			
		}elseif($task['action_type']==3){//代充
			if($user_task) return '该任务已经做过';
			$data = array('atid'=>$aid,'uid'=>$uid,'complete_status'=>2,'reward_status'=>0,'complete_time'=>time());
			$success = ActivityTaskUser::saveAddOrUpdate($data);
			$success_message = '任务已提交,请耐心等待客服人员审核';
			return $success ? $success_message : '任务失败';
		}
		return '任务失败';
	}
	
	public static function updateShareProgress()
	{
		$aid = Input::get('aid');
		$uid = Input::get('uid');
		$click_ip = Input::get('ip');
		if(!$aid || !$uid || !$click_ip) return 'params error';
		$saved = ActivityShareHistory::saveForNotExists($aid,$uid,$click_ip);
		if(!$saved) return 'db error';

		$time = time();
		$search['id'] = $aid;
		$search['start_time'] = $time;
		$search['end_time'] = $time;
		$search['is_show'] = 1;		
		$task = ActivityTask::findOne($search);		
		if(!$task) return true;
		$ip_times = ActivityShareHistory::getIpCount($aid,$uid);
		$limit_times = (isset($task['complete_condition']['ip_limit_times']) && $task['complete_condition']['ip_limit_times']) ? (int)$task['complete_condition']['ip_limit_times'] : 3;
		if($ip_times < $limit_times) return true;		
		$user_task = ActivityTaskUser::findOne(array('atid'=>$aid,'uid'=>$uid));
	    if($task['action_type']==2){//分享
			if(!$user_task) return true;
			if($user_task['complete_status']==1) return true;
			//$data = array('atid'=>$aid,'uid'=>$uid,'complete_status'=>1,'complete_time'=>time());
			$success = ActivityTaskUser::updateCompleteStatus($user_task['id'],1,true);
			//游币奖励
			if($success){
				$money = $task['money'];
				$info = '任务奖励:'.$task['title'].'';
				$reward_success = MoneyService::doAccount($uid,$money,'reward',$info);					
			    if($reward_success) {
			    	ActivityTaskUser::updateStatus($user_task['id'],array('reward_status'=>1,'reward_time'=>time()));
			    	$title = $task['title'].'任务完成';
			    	$message = '完成'.$task['title'].'任务奖励'.$money.'游币';
			    	self::sendTaskMessage($uid,$title,$message,23);
			    }
			}
			//$success_message = '任务完成,您获得'.$money.'游币';
			return $success ? true : 'status error';
			
		}
		return true;
	}
	
	/**
	 *
	 * @param int $uid
	 * @param string $title
	 * @param string $message
	 * @param string $linkType 21:我的礼包,22:我的商品,23:我的游币,24:任务详情,25:任务中心
	 * @param string $link
	 */
	public static function sendTaskMessage($uid,$title,$message,$linkType='',$link='')
	{
		//发送消息
		$info = UserDevice::getNewestInfoByUid($uid);
		if(!$info) return;
		$channelId = $info['channel_id'];
		$userId = $info['device_id'];
		$append = array('msg'=>$message,'linktype'=>$linkType,'link'=>$link);		
		BaiduPushService::pushUnicastMessage($title,'',16,-1,0,$uid, $channelId, $userId,$append,false,true);
	}
	
	public static function uploadScreenshot()
	{
		$aid = Input::get('aid');
	    $uid = Input::get('uid');
	    $files = Input::file();	    
	    $sfiles = Input::get('files');
	    $time = time();
		$search['id'] = $aid;
		$search['start_time'] = $time;
		$search['end_time'] = $time;
		$search['is_show'] = 1;

        //验证是否在黑名单
        $idcode  = Input::get('idcode');
        if(isset($idcode)&&!empty($idcode)){
            if(self::check_blacklist_by_idcode($idcode)){
                return "已加入黑名单";
            }
        }

		$task = ActivityTask::findOne($search);

		if(!$task) return '任务不存在';
		$user_task = ActivityTaskUser::findOne(array('atid'=>$aid,'uid'=>$uid));
	    if($task['action_type']==1){//试玩
			if($task['complete_type']=='screenshot'){//截屏
				if($user_task) return '该任务已经做过';
				if(!$files && !$sfiles) return '至少上传一张图片';
				$inserts = array();
				if($files){
					$dir = '/userdirs/screenshot/' . date('Y') . '/' . date('m') . '/';
		            $path = storage_path() . $dir;
					foreach($files as $file){
						$new_filename = date('YmdHis') . str_random(4);
						$mime = $file->getClientOriginalExtension();			
						$file->move($path,$new_filename . '.' . $mime );
						$img = $dir . $new_filename . '.' . $mime;
						$inserts[] = array('uid'=>$uid,'atid'=>$aid,'img'=>$img,'create_time'=>time());
					}
				}elseif($sfiles){
					$sfiles = explode(',',$sfiles);
					foreach($sfiles as $file){
						$inserts[] = array('uid'=>$uid,'atid'=>$aid,'img'=>$file,'create_time'=>time());
					}
				}
				if($inserts){
					ActivityTaskUserScreenshot::add($inserts);
				}
				$data = array('atid'=>$aid,'uid'=>$uid,'complete_status'=>2,'reward_status'=>0,'complete_time'=>time());
				$success = ActivityTaskUser::saveAddOrUpdate($data);
				$success_message = '任务已提交,请耐心等待客服人员审核';
			    return $success ? $success_message : '任务失败';
			}
		}
		return '任务失败';
	}
	
	public static function clearFinishShareTask()
	{
		$atids = ActivityTask::buildSearch(array('action_type'=>2,'ended'=>time()),1,100)->lists('id');
		if($atids){
			ActivityTaskUser::buildSearch(array('in_atid'=>$atids,'complete_status'=>2))->update(array('complete_status'=>3));
		}
	}

    /*
     * 验证用户是否在黑名单
     * return true: 已入黑名单； false: 不在黑名单
     */
    public static function check_blacklist_by_idcode($idcode)
    {
        $res = TaskService::find_blacklist_by_key('idcode',$idcode);
        $now = date("Y-m-d");
        if($now>=$res['createtime']&&$now<=$res['endtime']){
            return true;
        }else{
            return false;
        }
    }
}