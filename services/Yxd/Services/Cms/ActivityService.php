<?php
namespace Yxd\Services\Cms;

use Yxd\Services\UserFeedService;
use Yxd\Services\UserService;
use Yxd\Services\Service;
use Yxd\Models\Cms\Game;
use Yxd\Services\Models\Activity;
use Yxd\Services\Models\ActivityAsk;
use Yxd\Services\Models\ActivityAskAccount;
use Yxd\Services\Models\ActivityHunt;
use Yxd\Services\Models\ActivityHuntAccount;

/**
 * 有奖问答
 */
class ActivityService extends Service
{
	/**
	 * 已结束活动列表
	 */
	public static function getOverList($gid,$page=1,$pagesize=10)
	{
		$tb = Activity::db()->where('status','=',1);
		if($gid>0) $tb = $tb->where('game_id','=',$gid);
		$now = time();
		$tb = $tb->where('enddate','<=',$now);
		
		$total = $tb->count();
		$asks = $tb->orderBy('enddate','desc')->forPage($page,$pagesize)->get();
		return array('result'=>$asks,'total'=>$total);
	}
	/**
	 * 进行中活动列表
	 */
	public static function getDoingList($gid)
	{
		$start = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$end = mktime(23,59,59,date('m'),date('d'),date('Y'));
		$now = time();
		$tb = Activity::db()->where('status','=',1);
		
		if($gid>0) $tb = $tb->where('game_id','=',$gid);
		$tb = $tb->where('startdate','<=',$now)->where('enddate','>=',$now);
		$total = $tb->count();
		$asks = $tb->orderBy('sort','desc')->orderBy('id','desc')->get();
		return array('result'=>$asks,'total'=>$total);
	}

    /**
     * 搜索活动列表
     * @param $gids
     * @param int $page
     * @param int $pagesize
     * @return array
     */
	public static function searchList($gids,$page=1,$pagesize=10)
	{
		$tb = Activity::db()->whereIn('game_id',$gids);

		$total = $tb->count();
		$act = $tb->orderBy('sort','desc')->orderBy('id','desc')->forPage($page,$pagesize)->get();
		return array('result'=>$act,'total'=>$total);
	}
	
	/**
	 * 有奖问答详情
	 */
	public static function getAskDetail($id,$uid=0)
	{
		$activity = Activity::db()->where('id','=',$id)->first();		
		$ask = ActivityAsk::db()->where('id','=',$id)->first();
		if(!$ask){
			return null;
		}
		//奖品
		$ask['prizes'] = json_decode($ask['reward'],true);
		$prize_ids = array();
		foreach($ask['prizes'] as $key=>$row){
			$prize_ids[] = $row['prize_id'];
		}
		$_prizes = ActivityPrize::db()->whereIn('id',$prize_ids)->get();
	    $prizes = array();
		foreach($_prizes as $row){
			$prizes[$row['id']] = $row;
		}
		foreach($ask['prizes'] as $key=>$row){
			$row['prize_name'] = $prizes[$row['prize_id']]['name'];
			$row['pic'] = self::joinImgUrl($prizes[$row['prize_id']]['listpic']);
			$ask['prizes'][$key] = $row;
		}
		//问题
		$questions = ActivityAskQuestion::db()->where('ask_id','=',$id)->where('status','=',1)->orderBy('sort','asc')->get();
		$ask['questions'] = $questions;
		$myask = ActivityAskAccount::db()->where('ask_id','=',$id)->where('uid','=',$uid)->count();
		$ask['hasAttended'] = $myask>0 ? 1 : 0;
		$uids = ActivityAskAccount::db()->where('ask_id','=',$id)->orderBy('addtime','desc')->forPage(1,10)->distinct()->lists('uid');
		$uids = array_unique(($uids));
		$ask['attendCount'] = ActivityAskAccount::db()->where('ask_id','=',$id)->count();
		
		if($uids){
			if($uid && in_array($uid,$uids)){
				$ask['hasAttended'] = 1;
			}			
			$users = UserService::getBatchUserInfo($uids);
			$ask['attendUsers'] = $users;
		}else{
			$ask['attendUsers'] = array();
		}
		
		return array_merge($activity,$ask);
	}
	
	public static function getInfo($activity_id)
	{
		return Activity::db()->where('id','=',$activity_id)->first();
	}
	
	/**
	 * 提交回答
	 */
	public static function doCommit($uid,$ask_id,$answer)
	{
		//是否已经参加过活动
		$myask = ActivityAskAccount::db()->where('ask_id','=',$ask_id)->where('uid','=',$uid)->count();
		if($myask) return false;
		
		$ids = array();
		$user_result = array();
		foreach($answer as $row){
			$ids[] = $row['numid'];
			$user_result[$row['numid']] = $row['choice'];
		}
		$result = ActivityAskQuestion::db()->where('ask_id','=',$ask_id)->where('status','=',1)->lists('answer','id');
		$total_score = count($result);
		if($total_score != count($ids)){
			return -1;
		}
		$score = 0;
		foreach($result as $key=>$val){
			if(isset($user_result[$key]) && strcasecmp($user_result[$key],$val)==0){
				$score++;
			}
		}
		$data = array(
		    'uid'=>$uid,
		    'ask_id'=>$ask_id,
		    'answers'=>json_encode($user_result),//回答
		    'result'=> ceil(($score/$total_score)*100),//成绩
		    'addtime'=>time(),
		    'reward_status'=>0
		);
		$id = ActivityAskAccount::db()->insertGetId($data);
		if($id>0){
			//产生动态
			UserFeedService::makeFeedActivity($uid, $ask_id);
			//发送通知
		}
		return $id>0 ? true : false;
	}	
}