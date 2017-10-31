<?php
use Yxd\Services\CreditService;
use Yxd\Services\TaskService;
use Yxd\Services\UserService;
use Yxd\Modules\System\SettingService;
use Illuminate\Support\Facades\Input;
/**
 * 任务
 */
class TaskController extends BaseController
{
	/**
	 * 任务列表
	 */
	public function home()
	{
		$uid = Input::get('uid');
		$tasks = array();
		
		$user = UserService::getUserRealTimeCredit($uid);
		$level = UserService::getLevelInfo($uid, $user['experience']);
		$tasks['coincenteruser'] = array(
		    'money'=>$user['score'],
		    'nextlevel'=>($level['end'] - $user['experience']),
		    'maxlevel'=>$level['end']
		);
		//连续签到奖励
		$checkin_credit = SettingService::getConfig('checkin_setting');		
		$checkin_reward_list = isset($checkin_credit['data']) ?  $checkin_credit['data'] : array();
		$coincentercoin = array();
		foreach($checkin_reward_list as $coin){
			$time = time();
			$startdate = mktime(0,0,0,2,14,2015);
			$enddate = mktime(0,0,0,2,25,2015);
			if($time>$startdate && $time<$enddate){
				$coin = $coin * 2;
			}
			$coincentercoin[] = array('coincentercoin'=>$coin);
		}
		$tasks['checkinlist'] = $coincentercoin;
		//用户签到记录		
		$result = TaskService::getLastWeekCheckin($uid);
		if($result){
			$tasks['checkincount'] = count($result);
		}else{
			$tasks['checkincount'] = 0;
		}
		//常规任务
		$tasks['commontasks'] = TaskService::getList(2, $uid);
		foreach($tasks['commontasks'] as $key=>$row){
			$task = array();
			$task['content'] = $row['step_name'];
			$task['tourency'] = $row['reward'] ? '+' . $row['reward']['score'] . '游币' : '';
			$task['iscomplete'] = strval($row['iscomplete']);
			$tasks['commontasks'][$key] = $task;
		}
		//日常任务
		$tasks['dailytasks'] = TaskService::getList(1, $uid);
	    foreach($tasks['dailytasks'] as $key=>$row){
			$task = array();
			$task['content'] = $row['step_name'];
			$task['tourency'] = $row['reward'] ? '+' . $row['reward']['score'] . '游币' : '';
			$task['iscomplete'] = strval($row['iscomplete']);
			$tasks['dailytasks'][$key] = $task;
		}
	    //推广任务
		$tasks['tuiguangtasks'] = TaskService::getList(3, $uid,Input::get('version','3.0.0'));
	    foreach($tasks['tuiguangtasks'] as $key=>$row){
			$task = array();
			$task['content'] = $row['step_name'];
			$task['tourency'] = $row['reward'] ? '+' . $row['reward']['score'] . '游币' : '';
			$task['iscomplete'] = strval($row['iscomplete']);
			$tasks['tuiguangtasks'][$key] = $task;
		}
		$tasks['ischeckin'] = (int)TaskService::isExistsCheckin($uid);
		//用户的注册码
		$tasks['zhucema'] = TaskService::getZhucefa($uid);
		$tasks['copystring'] = str_replace('{zhucema}',TaskService::getZhucefa($uid),'请到www.youxiduo.com下载游戏多，安装后在注册时填写邀请码“{zhucema}”，新用户注册成功您就会获得30游币哦！');
		$tasks['zhucecount'] = UserService::getInviteCount($uid);
		return $this->success(array('result'=>$tasks));
	}
	
	/**
	 * 常规任务
	 */
	public function normal()
	{
		$uid = Input::get('uid');
		$tasks = TaskService::getList(2, $uid);
		return $this->success(array('result'=>$tasks));
	}
	
	/**
	 * 每日任务
	 */
	public function everyday()
	{
		$uid = Input::get('uid');
		$tasks = TaskService::getList(1, $uid);
		return $this->success(array('result'=>$tasks));
	}
	
	/**
	 * 可接受任务数
	 */
	public function number()
	{
		$uid = Input::get('uid');
		$count = TaskService::getCanExecTaskCount($uid);
		return $this->success(array('totalCount'=>$count));
	}
	
	/**
	 * 每日签到
	 */
	public function checkin()
	{
		$uid = Input::get('uid',0);
		if(!$uid){
			return $this->fail(11211,'未登录');
		}
		$result = TaskService::doCheckin($uid);
		if($result===true){
			$checkinlist = TaskService::getContinuousCheckin($uid);
			$total = count($checkinlist);
			
			$checkin_credit = SettingService::getConfig('checkin_setting');		
			$checkin_reward_list = isset($checkin_credit['data']) ?  $checkin_credit['data'] : array();
			$coincentercoin = array();
			foreach($checkin_reward_list as $coin){
				$coincentercoin[] = array('coincentercoin'=>$coin);
			}
			if($total<=7){
				$score= $coincentercoin[$total-1]['coincentercoin'];
			}else{
				$score= $coincentercoin[7]['coincentercoin'];
			}
                        $time = time();
                        $startdate = mktime(0,0,0,2,14,2015);
                        $enddate = mktime(0,0,0,2,25,2015);
                        
                        if($time>$startdate && $time<$enddate){
                            $score = $score * 2;
                            $msg = '春节签到奖励双倍游币'.$score;
                        }else{
			    $msg = '签到成功！ 游币+' . $score . '';
                        }
			//return $this->success(array('result'=>$out));
			return $this->fail(600,$msg);
		}else{
			return $this->fail(11211,'今日已经签到');
		}
	}
	
	public function share()
	{
		$uid = Input::get('uid');
		if(!$uid){
			return $this->fail(11211,'分享成功');
		}
		if(TaskService::checkShareLimit($uid)===true){
			return $this->fail(11211,'分享成功');
		}
		$msg = '分享成功 经验+5';
		$score = TaskService::doShare($uid);
		CreditService::doUserCredit($uid,CreditService::CREDIT_RULE_ACTION_SHARE);
		if(is_numeric($score)){
			$msg = '分享3次成功 游币+' . $score;
			return $this->fail(600,$msg);
		}		
		return $this->fail(11211,$msg);
	}
	
	/**
	 * 签到记录
	 */
	public function checkin_log()
	{
		$uid = Input::get('uid');
		$result = TaskService::getLastWeekCheckin($uid);
		if($result){
			$out = array();
			$total = count($result);
			foreach($result as $row){
				$out[] = date('Y-m-d',$row);
			}
			return $this->success(array('result'=>$out,'total'=>$total));
		}else{
			return $this->fail(11211,'没有签到记录');
		}
	}
}
