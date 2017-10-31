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
use Youxiduo\Base\BaseService;
use Youxiduo\Android\Model\Checkinfo;
use Youxiduo\Android\Model\CheckinsTaskUser;
use Youxiduo\Android\Model\CheckinsTask;
use Youxiduo\Android\BaiduPushService;
use Youxiduo\Android\Model\UserDevice;
use Youxiduo\Android\Model\CheckinsMoney;

class CheckinsService extends BaseService
{
	public static function doTask($uid)
	{
	    $search = array('start_time'=>time(),'end_time'=>time(),'is_show'=>1);
		$tasks = CheckinsTask::searchList($search,1,4,array('sort'=>'desc','id'=>'desc'));
		$res = CheckinsTaskUser::searchList(array('uid'=>$uid),1,31,array('id'=>'desc'));
		$task_ids = array();
		foreach($res as $row){
			$task_ids[] = $row['ctid'];
		}		
		$running_days = count(Checkinfo::getContinuousCheckin($uid,date('j')));//连续
		$cumulative_days = (int)CheckInfo::getCurrentMonthCheckinsTimes($uid);//累计
	    foreach($tasks as $row){
			$exists = ($task_ids && in_array($row['id'],$task_ids)) ? true : false;
			if($exists) continue;//已完成的任务则跳过
			$money = $row['reward_value'];
			$type = $row['type'];
			$days = $row['reward_days'];
			$add_task = false;
			if($type=='running'){//连续
				$st = date_create(date('Y-m-d',$row['start_time']));
				$et = date_create(date('Y-m-d'));
				$diff = date_diff($st,$et);
				if($diff->days >= $days && $running_days>=$days){
					$add_task = true;
					$info = '任务奖励:完成连续签到' . $days . '天任务';
					$message = '尊敬的用户，您已经完成《'.$row['title'].'》任务，获得了'.$money.'游币，请前往游币中心查看。';
				}
			}elseif($type=='cumulative'){//累计
			    if($cumulative_days>=$days){
					$add_task = true;
					$info = '任务奖励:完成累计签到' . $days . '天任务';
					$message = '尊敬的用户，您已经完成《'.$row['title'].'》任务，获得了'.$money.'游币，请前往游币中心查看。';
				}
			}
			if($add_task==true){
				$data = array('uid'=>$uid,'ctid'=>$row['id'],'status'=>0);
				$success = CheckinsTaskUser::add($data);
				if($success){					
					$reward_success = MoneyService::doAccount($uid,$money,'reward_checkins',$info);
					CheckinsMoney::addTodayMoney($uid,$type,$money);
					if($reward_success){
						CheckinsTaskUser::updateStatus($success,1);
						//发送消息
						$info = UserDevice::getNewestInfoByUid($uid);
						if(!$info) continue;
						$title = '签到任务完成';
						$channelId = $info['channel_id'];
						$userId = $info['device_id'];
						$append = array('msg'=>$message,'linktype'=>23,'link'=>0);
						BaiduPushService::pushUnicastMessage($title,'',16,-1,0,$uid, $channelId, $userId,$append,false,true);
						
					}
				}
			}
		}
		return true;
	}
}