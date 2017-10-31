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

use Youxiduo\V4\User\MoneyService;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Youxiduo\Base\BaseService;
use Youxiduo\System\Model\AppConfig;
use Youxiduo\Android\Model\Task;
use Youxiduo\Android\Model\TaskAccount;
use Youxiduo\Android\Model\ShareLimit;
use Youxiduo\Android\Model\Checkinfo;
use Youxiduo\Android\CheckinsService;
use Youxiduo\Android\Model\CheckinsMoney;
use Youxiduo\User\Model\Account;
use Youxiduo\Android\Model\TaskBlacklist;
use Youxiduo\Android\Model\ActivityTaskLimit;

class TaskService extends BaseService
{
	const TASK_EXEC_TYPE_EVERYDAY = 1;//每日任务
	const TASK_EXEC_TYPE_ONCE = 2;//一次性任务
	const TASK_EXEC_TYPE_LIMIT = 3;//次数限制任务
	
	/**
	 * 执行每日登录任务
	 */
	public static function doLogin($uid)
	{
		self::doTask($uid,'login',self::TASK_EXEC_TYPE_EVERYDAY);
		return self::trace_result(array('result'=>true));
	}
	
    /**
	 * 执行上传头像任务
	 */
	public static function doUploadAvatar($uid)
	{
		$version = Input::get('version');
		if($version != '2.9.1'){
		    self::doTask($uid,'upload-avatar',self::TASK_EXEC_TYPE_ONCE);	
		}
		return self::trace_result(array('result'=>true));	
	}
	
	/**
	 * 执行上传背景任务
	 */
	public static function doUploadHomebg($uid)
	{
	    self::doTask($uid,'upload-homebg',self::TASK_EXEC_TYPE_ONCE);
	    return self::trace_result(array('result'=>true));
	}
	
	/**
	 * 执行完善资料任务
	 */
	public static function doPerfectInfo($uid)
	{
		self::doTask($uid,'edit-info',self::TASK_EXEC_TYPE_ONCE);
		return self::trace_result(array('result'=>true));	    
	}
	
	public static function doPerfectUserInfo($uid,$input)
	{
		$version = Input::get('version');
		if($version != '2.9.1'){
			if(isset($input['nickname']) && !empty($input['nickname'])){
				self::doTask($uid,'nickname',self::TASK_EXEC_TYPE_ONCE);
			}
			
			if(isset($input['summary']) && !empty($input['summary'])){
				self::doTask($uid,'summary',self::TASK_EXEC_TYPE_ONCE);
			}
			
			if(isset($input['birthday']) && !empty($input['birthday'])){
				self::doTask($uid,'birthday',self::TASK_EXEC_TYPE_ONCE);
			}
			
			if(isset($input['sex']) && !empty($input['sex'])){
				self::doTask($uid,'sex',self::TASK_EXEC_TYPE_ONCE);
			}
			
			if(isset($input['province']) && !empty($input['province']) && isset($input['city']) && !empty($input['city']) && isset($input['region']) && !empty($input['region'])){
				Account::modifyUserInfo($uid,array('province'=>$input['province'],'city'=>$input['city'],'region'=>$input['region']));
				self::doTask($uid,'location',self::TASK_EXEC_TYPE_ONCE);
			}
		}

		return true;
	}
	
	/**
	 * 执行游戏下载任务
	 */
	public static function doDownloadGame($uid)
	{
		self::doTask($uid,'download',self::TASK_EXEC_TYPE_EVERYDAY);
		return self::trace_result(array('result'=>true));
	}
	
    /**
	 * 执行游戏评论任务
	 */
	public static function doGameComment($uid)
	{
		self::doTask($uid,'game-comment',self::TASK_EXEC_TYPE_EVERYDAY);
		return self::trace_result(array('result'=>true));
	}
	
    /**
	 * 执行每日分享任务
	 */
	public static function doShare($uid)
	{
		self::doTask($uid,'share',self::TASK_EXEC_TYPE_EVERYDAY);
		return self::trace_result(array('result'=>true));
	}
	
    public static function checkShareLimit($uid)
	{
		//判断奖励次数
		$end = mktime(23,59,59,date('m'),date('d'),date('Y'));
		$start = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$expire = $end - time();
		$limit = ShareLimit::db()->table('share_limit')->where('uid','=',$uid)->where('ctime','>',$start)->count();		
		if($limit>=3){
			return true;
		}else{
			ShareLimit::db()->insert(array('uid'=>$uid,'ctime'=>time()));
			return false;
		}
	}
	
    /**
	 * 执行任务
	 */
	protected static function doTask($uid,$action,$execType)
	{
		$finish = false;
		$addtask = false;
		$flag = false;
		$task = Task::db()->where('action','=',$action)->first();
		if($task){
			$reward = json_decode($task['reward'],true);
			$condition = json_decode($task['condition'],true);
			if(isset($condition['closed']) && intval($condition['closed'])==1) return false;
			if($execType == self::TASK_EXEC_TYPE_ONCE){//一次性任务
			    $user_task = TaskAccount::db()->where('uid','=',$uid)->where('task_id','=',$task['id'])->first();
			    if(!$user_task) {
			    	$finish = true;
			    	$addtask = true;
			    }
			}elseif($execType == self::TASK_EXEC_TYPE_EVERYDAY){//每日任务
				$start = mktime(0,0,0,date('m'),date('d'),date('Y'));
				$end = mktime(23,59,59,date('m'),date('d'),date('Y'));				
				$user_task = TaskAccount::db()->where('uid','=',$uid)->where('task_id','=',$task['id'])->where('ctime','>=',$start)->where('ctime','<=',$end)->first();
				$times = TaskAccount::db()->where('uid','=',$uid)->where('task_id','=',$task['id'])->where('ctime','>=',$start)->where('ctime','<=',$end)->count();
				$limit = (int)$condition[$action];
				$max_times = isset($condition['max_times']) ? (int)$condition['max_times'] : 1;
				if($limit==1){					
					if(!$user_task){//没有做完该任务
				        $finish = true;
				        $addtask = true;
					}elseif($times<$max_times){//已完成该任务,判断是否达到最大奖励次数						
						$addtask = true;
						$finish = true;
					}
				}elseif($limit>1){//完成次数验证
					$cycle = $times/$limit;//(除法运算)
					$cycle_times = $times%$limit;//周期内完成次数(取余)
					if($cycle<$max_times){
						if($limit>$cycle_times) $addtask = true;
						if($limit === ($cycle_times+1)) $finish = true;
					}
					
				}
				
			}elseif($execType == self::TASK_EXEC_TYPE_LIMIT){
//				$times = self::dbClubMaster()->table('task_account')->where('uid','=',$uid)->where('task_id','=',$task['id'])->count();
//				$limit = (int)$condition[$action];
//				if($times >= $limit) $finish = true;
//				if($times<$limit) $addtask = true;
				$finish = true;
				$addtask = true;
				$flag = true;
			}
			
			if($addtask===true){
				$user_task = array();
				$user_task['task_type'] = $task['type'];
				$user_task['task_id'] = $task['id'];
				$user_task['status'] = 1;
				$user_task['uid'] = $uid;
				$user_task['receive'] = 1;
				$user_task['ctime'] = time();
				TaskAccount::db()->insertGetId($user_task);				
			}
		    if($finish==true) {//获取奖励 
		    	$info = '完成' . $task['typename'] . $task['step_name'] . ',获得游币' . $reward['score'] . '个';
		    	if($flag){
		    		CreditService::doUserMoney($uid,(int)$reward['score'],(int)$reward['experience'],$action,$info);
		    		$money = $reward['score'];
		    		$money && MoneyService::doAccount($uid,$money,$action,$info);
		    	}else{
		    		CreditService::doUserMoney($uid,(int)$reward['score'],(int)$reward['experience'],'task_'.$action,$info);
		    		$money = $reward['score'];
		    		$money && MoneyService::doAccount($uid,$money,$action,$info);
		    	}
				return (int)$reward['score'];
			}
			return true;
		}
		return false;
	}
	
    /**
	 * 签到
	 */
	public static function doCheckin($uid)
	{
		$version = Input::get('version');
		if(self::isExistsCheckin($uid)){
			return self::trace_error('E1','今日已经签到过了!');
		}else{
			try{
				$id = Checkinfo::db()->insertGetId(array('uid'=>$uid,'ctime'=>time(),'cdate'=>date('Ymd')));
				
				if($id) { 
					$success = self::doEveryCheckin($uid);
					if($success){
						self::doLogin($uid);
			            if($version != '2.9.1'){
						    CheckinsService::doTask($uid);
						}
						Checkinfo::db()->where('id','=',$id)->update(array('is_reward'=>1));
					}
				}
				$money = CheckinsMoney::getTodayMoney($uid);
				$message = '';
				$result = array(
				    'money'=>$money,'message'=>$message
				);
				
				if($version != '2.9.1') return $id ? self::trace_result(array('result'=>$result)) : self::trace_error('E1','签到失败!');
				return $id ? self::trace_result(array('result'=>$result)) : self::trace_error('E1','签到失败!');
			}catch(\Exception $e){
				Log::error($e);
				return self::trace_error('E1','签到成功!');
			}
		}
	}
	
    /**
	 * 是否已经签到
	 */
	public static function isExistsCheckin($uid)
	{
		$exists = Checkinfo::db()->where('uid','=',$uid)->where('cdate','=',date('Ymd'))->first();
		return $exists ? true : false;
	}
	
    /**
	 * 执行每日签到任务
	 */
	public static function doEveryCheckin($uid)
	{
		$checkin_credit['data'] = array(
		    'first_day'=>5,
		    'second_day'=>6,
		    'third_day'=>7,
		    'fourth_day'=>8,
		    'fifth_day'=>9,
		    'sixth_day'=>10,
		    'seventh_day'=>10,
		    'greater_seven_day'=>10
		);
		if(!$checkin_credit) return null;
		$continuous_checkin = self::getLastWeekCheckin($uid);
		$continuous_count = count($continuous_checkin);
		//print_r($continuous_checkin);
		if($continuous_count>=1){
			$score = 0;
			switch($continuous_count){
				case 1:
					$score = isset($checkin_credit['data']['first_day']) ? (int)$checkin_credit['data']['first_day'] : 0;
					break;
				case 2:
					$score = isset($checkin_credit['data']['second_day']) ? (int)$checkin_credit['data']['second_day'] : 0;
					break;
				case 3:
					$score = isset($checkin_credit['data']['third_day']) ? (int)$checkin_credit['data']['third_day'] : 0;
					break;
				case 4:
					$score = isset($checkin_credit['data']['fourth_day']) ? (int)$checkin_credit['data']['fourth_day'] : 0;
					break;
				case 5:
					$score = isset($checkin_credit['data']['fifth_day']) ? (int)$checkin_credit['data']['fifth_day'] : 0;
					break;
				case 6:
					$score = isset($checkin_credit['data']['sixth_day']) ? (int)$checkin_credit['data']['sixth_day'] : 0;
					break;
				case 7:
					$score = isset($checkin_credit['data']['seventh_day']) ? (int)$checkin_credit['data']['seventh_day'] : 0;
					break;
				default:
					$score = isset($checkin_credit['data']['greater_seven_day']) ? (int)$checkin_credit['data']['greater_seven_day'] : 0;
					break;
			}
			if($score){
				$time = time();
				$startdate = mktime(0,0,0,1,1,2015);
				$enddate = mktime(0,0,0,1,4,2015);
				$info = '签到奖励游币'.$score;
				if($time>$startdate && $time<$enddate){
					$score = $score * 2;
					$info = '元旦签到奖励双倍游币'.$score;
				}
				//CreditService::handOpUserCredit($uid,$score,0,'checkin',$info);
				CheckinsMoney::addTodayMoney($uid,'today',$score);
				$result = MoneyService::doAccount($uid,$score,'reward_checkins','任务奖励:每日签到，连续第'.$continuous_count.'天');				
				return $result;
			}
			return false;
		}
		return false;
	}
	
    /**
	 * 获取最近七天内连续签到记录
	 */
	public static function getLastWeekCheckin($uid)
	{
		$today_start = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$days = 365;
		$start = $today_start - (60*60*24*$days);
		$week = array();
		for($i=0;$i<$days;$i++){
			$week[] = $today_start - (60*60*24*($i+1));
		}		
		$list = Checkinfo::db()->where('uid','=',$uid)->where('ctime','>=',$start)->orderBy('ctime','desc')->lists('ctime');
		$checkin_list = array();
		$index = 0;
		foreach($list as $time){
			if($time>=$today_start){
				$checkin_list[] = $time;
			}else{			
				if($time>=$week[$index]){
					$checkin_list[] = $time;
					$index++;
				}else{
				    break;
			    }
			}
		}
		return $checkin_list;
	}
	
	/**
	 * 连续签到记录
	 */
	public static function getContinuousCheckin($uid)
	{
		$today_start = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$list = Checkinfo::db()->where('uid','=',$uid)->orderBy('ctime','desc')->take(8)->lists('ctime');
		$count = count($list);
		$continuous = array();
		for($i=0;$i<$count;$i++){
			$continuous[] = $today_start - (60*60*24*($i+1));
		}
		$checkin_list = array();
		$index = 0;
		foreach($list as $time){
			if($time>=$today_start){
				$checkin_list[] = $time;
			}else{			
				if($time>=$continuous[$index]){
					$checkin_list[] = $time;
					$index++;
				}else{
				    break;
			    }
			}
		}
		return $checkin_list;
	}
    /*
     * 保存黑名单
     */
    public static function doSaveBlacklist($data)
    {
        //查询用户名和设备编号
        $limit_res = self::get_limit_by_uid($data['uid']);
        if($limit_res){
            $data['phonekey']=$limit_res['idcode'];
        }
        $res = TaskBlacklist::save($data);
        return $res;
    }
    /*
     * $type 为 字段名（id`uid`idcode..）
     * 根据key查找单挑黑名单数据
     */
    public static function find_blacklist_by_key($type,$key)
    {
        if(isset($type)&&!empty($type)){
            return TaskBlacklist::find_by_key($type,$key);
        }
    }
    /*
     * 获取黑名单列表
     */
    public static function find_blacklist($search,$pageIndex=1,$pageSize=10)
    {
        return TaskBlacklist::find_blacklist($search,$pageIndex,$pageSize);
    }

    /*
     * 获取黑名单列表总数
     */
    public static function find_blacklist_count($search)
    {
        return TaskBlacklist::find_blacklist_count($search);
    }

    /*
    * 获取限制表中的数据（idcode..）
    */
    public static function get_limit_by_uid($uid)
    {
        return ActivityTaskLimit::get_limit_by_uid($uid);
    }

    /*
    * 删除黑名单
    */
    public static function del_blacklist($id)
    {
        return TaskBlacklist::del_blacklist($id);
    }



}