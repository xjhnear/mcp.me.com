<?php
namespace Youxiduo\Activity\Share;
use Illuminate\Support\Facades\DB;
use Youxiduo\Helper\Utility;
use Youxiduo\Chat\ChatService;
use Illuminate\Support\Facades\Config;


class ActivityService extends Service
{
	const TB_ACTIVITY = 'activity';
	const TB_USER_ACTIVITY = 'user_activity';
	const TB_USER_ACTIVITY_KILL_HISTORY = 'user_activity_kill_history';

	/**
	 * 获取分享缓存
	 * @return mixed
     */
	public static function getWxShareCache(){
		return self::db()->table('wx_share_cache')->orderBy('id','asc')->first();
	}

	public static function addWxShareCache($token,$ticket,$expire){
		$cache = self::getWxShareCache();
		if($cache){
			self::db()->table('wx_share_cache')->update(array('access_token'=>$token,'jsapi_ticket'=>$ticket,'expire_time'=>$expire));
		}else{
			self::db()->table('wx_share_cache')->insert(array('access_token'=>$token,'jsapi_ticket'=>$ticket,'expire_time'=>$expire));
		}
	}

	public static function getValidActivityInfo($hashcode,$starttime=false,$endtime=false){
		if(!$hashcode) return false;
		$query = self::db()->table('activity');
		if($starttime) $query->where('starttime','>=',$starttime);
		if($endtime) $query->where('endtime','<=',$endtime);
		$activity_info = $query->where('hashcode',$hashcode)->where('is_show',1)->first();
		if(!$activity_info) return false;
		return $activity_info;
	}

	public static function getAllWxActivityInfo(){
		return self::db()->table('activity')->where('is_show',1)->whereIn('share_times',array(1,0))->get();
	}

	public static function getActivityById($id){
		return self::db()->table('activity')->where('id',$id)->first();
	}

	public static function getUserActivityById($id){
		return self::db()->table('user_activity')->where('id',$id)->first();
	}

	/**
	 * 获取单次活动每个人的记录
	 * @param $uid
	 * @param $activity_id
	 * @param $adddate
	 * @return mixed
     */
	public static function getCurrentDayActivityRecord($uid,$activity_id,$adddate){
		return self::db()->table('user_activity')
			->where('uid',$uid)
			->where('activity_id',$activity_id)
			->where('adddate',$adddate)
			->first();
	}

	/**
	 * 获取累计活动每个人的记录
	 * @param $uid
	 * @param $activity_id
	 * @return mixed
     */
	public static function getMultiActivityRecord($uid,$activity_id){
		return self::db()->table('user_activity')
			->where('uid',$uid)
			->where('activity_id',$activity_id)
			->first();
	}


	/**
	 * 获取拆礼品记录
	 * @param $id
	 * @return bool
     */
	public static function getUserActivityInfo($id){
		if(!$id) return false;
		return self::db()->table('user_activity')->where('id',$id)->first();
	}

	/**
	 * 新增单次活动每个人的记录
	 * @param $data
	 * @return bool
     */
	public static function addCurrentDayActivityRecord($data){
		if(!$data) return false;
		return self::db()->table('user_activity')
			->insert($data);
	}

	/**
	 * 获取当天帮抢记录（每天单次）
	 * @param $user_activity_id
	 * @param $wx_session_id
	 * @param $datetime
	 * @return mixed
     */
	public static function getCurrentDayHelpInfo($user_activity_id,$wx_session_id,$datetime){
		return self::db()->table('user_activity_kill_history')
			->where('user_activity_id',$user_activity_id)
			->where('wx_session_id',$wx_session_id)
			->where('adddate',$datetime)
			->first();
	}

	public static function getCurrentDayHelpTotalNum($wx_session_id,$adddate){
		return self::db()->table('user_activity_kill_history')
			->where('wx_md5_session_id','=',md5($wx_session_id))
			->where('adddate','=',$adddate)
			->count();
	}

	/**
	 * 获取当天帮抢记录（累计统计）
	 * @param $user_activity_id
	 * @param $wx_session_id
	 * @return mixed
     */
	public static function getMultiHelpInfo($user_activity_id,$wx_session_id){
		return self::db()->table('user_activity_kill_history')
			->where('user_activity_id',$user_activity_id)
			->where('wx_session_id',$wx_session_id)
			->first();
	}

	/**
	 * 更新当天帮抢记录（每天单次）
	 * @param $uaid
	 * @param $data
	 * @return bool
	 */
	public static function helpProcess($uaid,$data){
		if(!$uaid || !$data) return false;
		DB::transaction(function()use($uaid,$data){
			ActivityService::db()->table('user_activity_kill_history')->insert($data);
        	$up_result = ActivityService::db()->table('user_activity')->where('id',$uaid)->where('last_blood','>',0)->decrement('last_blood',1,array('updatetime'=>time()));
			return $up_result;
		});
	}

	/**
	 * 更新帮抢记录（累计统计）
	 * @param $uaid
	 * @param $data
	 * @return bool
     */
	public static function multiHelpProcess($uaid,$data){
		if(!$uaid || !$data) return false;
		DB::transaction(function()use($uaid,$data){
			ActivityService::db()->table('user_activity_kill_history')->insert($data);
			$up_result = ActivityService::db()->table('user_activity')->where('id',$uaid)->increment('total_blood',1,array('updatetime'=>time()));
			return $up_result;
		});
	}

	/**
	 * 更新帮抢记录信息
	 * @param $id
	 * @param $data
	 * @return bool
     */
	public static function updateUserActivity($id,$data){
		if(!$id || !$data) return false;
		return self::db()->table('user_activity')->where('id',$id)->where('is_pickup',0)->update($data);
	}

	/**
	 * @param $to_id
	 * @param $msg
	 * @param string $type //users 个人
	 * @return mixed
	 */
	public static function sendAndroidMsg($to_id,$msg,$type='users'){
		$hxid_result = ChatService::registerHx($to_id);
        $hxid = $hxid_result['result'];
		$itfc_url = Config::get('app.android_chat_api_url') . "send-message";
		if(is_array($msg)){
			$msg = $msg['content'].$msg['giftcard'];
		}else{
			$msg = $msg;
		}
		$fileds = array(
			'targetType' => $type,
			'receiver' => $hxid,
			'msg' => $msg,
            'fromUid' => Config::get('app.android_admin_huanxin_id')
		);
		return Utility::loadByHttp($itfc_url,$fileds);
	}

	
	public static function getAllActivityToKV()
	{
		$result = self::db()->table(self::TB_ACTIVITY)->orderBy('id','desc')->lists('title','id');
		
		return $result;
	}
	
	public static function search($search,$pageIndex=1,$pageSize=10,$sort=array())
	{
		$total = self::buildSearch($search)->count();
		$result = self::buildSearch($search)->forPage($pageIndex,$pageSize)->orderBy('id','desc')->get();
		return array('result'=>$result,'totalCount'=>$total);
	}
	
    protected static function buildSearch($search)
	{
		$tb = self::db()->table(self::TB_ACTIVITY);
		
		return $tb;
	}
	
    /**
	 * 保存活动信息
	 */
	public static function saveInfo($data)
	{
		if(isset($data['id']) && $data['id']>0){
			$id = $data['id'];
			unset($data['id']);
			$data['hashcode'] = md5($id);
			self::db()->table(self::TB_ACTIVITY)->where('id','=',$id)->update($data);			
		}else{
			$data['addtime'] = time();
			$id = self::db()->table(self::TB_ACTIVITY)->insertGetId($data);
			self::db()->table(self::TB_ACTIVITY)->where('id','=',$id)->update(array('hashcode'=>md5($id)));
		}
		return $id;
	}
	
    /**
	 * 获取活动信息
	 */
	public static function getInfo($activity_id)
	{
		return self::db()->table(self::TB_ACTIVITY)->where('id','=',$activity_id)->first();
	}
	
    public static function searchUserActivity($search,$pageIndex=1,$pageSize=10,$sort=array())
	{
		self::db()->update('update yxd_user_activity  a , (select user_activity_id,count(*) as total from yxd_user_activity_kill_history group by user_activity_id) as b set a.total_click=b.total where a.id=b.user_activity_id');
		$total = self::buildSearchUserActivity($search)->count();
		$tb = self::buildSearchUserActivity($search)->forPage($pageIndex,$pageSize);
		if($sort && is_array($sort)){
			foreach($sort as $field=>$order){
			    $tb = $tb->orderBy($field,$order);
			}
		}
		$result = $tb->get();
		return array('result'=>$result,'totalCount'=>$total);
	}
	
    protected static function buildSearchUserActivity($search)
	{
		$tb = self::db()->table(self::TB_USER_ACTIVITY);
		if(isset($search['uid']) && $search['uid']){
			$tb = $tb->where('uid','=',$search['uid']);
		}
		if(isset($search['activity_id']) && $search['activity_id']){
			$tb = $tb->where('activity_id','=',$search['activity_id']);
		}
		return $tb;
	}
	
    public static function searchUserActivityHistory($search,$pageIndex=1,$pageSize=10,$sort=array())
	{
		$total = self::buildSearchUserActivityHistory($search)->count();
		$result = self::buildSearchUserActivityHistory($search)->forPage($pageIndex,$pageSize)->orderBy('id','desc')->get();
		return array('result'=>$result,'totalCount'=>$total);
	}
	
    protected static function buildSearchUserActivityHistory($search)
	{
		$tb = self::db()->table(self::TB_USER_ACTIVITY_KILL_HISTORY);
		if(isset($search['user_activity_id']) && $search['user_activity_id']){
			$tb = $tb->where('user_activity_id','=',$search['user_activity_id']);
		}
		return $tb;
	}
	
	public static function getUserActivitySort()
	{
		$tb = self::db()->table(self::TB_USER_ACTIVITY_KILL_HISTORY);
		return $tb->groupBy('user_activity_id')->select(DB::Raw('user_activity_id as id,count(*) as total'))->orderBy('total','desc')->lists('total','id');
	}
	
	public static function updateSendMessageStatue($id)
	{
		return self::db()->table('giftbag_card')->where('id','=',$id)->update(array('is_send'=>1));
	}
	
	public static function AutoSendMessage()
	{
	    $today = date('Ymd');
	    $result = self::db()->table('giftbag_card')->where('adddate','=',$today)->where('is_send','=',0)->get();
	    if($result){
		    foreach($result as $row){
			    if($row['uid'] == 0) continue;
				$res = ActivityService::sendAndroidMsg($row['uid'],$row['cardno'],'users');
				print_r($res);
				self::db()->table('giftbag_card')->where('id','=',$row['id'])->update(array('is_send'=>1));
			}
		}
	}
	
	public static function AutoSend()
	{
		$result = self::db()->table('user_activity')->where('activity_id','=',1)->where('last_blood','=',0)->where('is_pickup','=',0)->get();
		$today = date('Ymd',time());
		//print_r($result);
		if($result){
			foreach($result as $user_activity){
				$send_status = false;
				$send_giftbag = '';
				$bag_id = 0;
				$giftbag_info = GiftbagService::getGiftbagInfo($user_activity['activity_id']);
				if($giftbag_info){
					$valid_giftbags = GiftbagService::getValidGiftbagCard($giftbag_info['id'],$today);
					
					if($valid_giftbags){
						foreach ($valid_giftbags as $bag) {
							$update = GiftbagService::updateGiftbagStatus($bag['id'],$user_activity['uid']);
							if($update) {
								$send_status = true;
								$send_giftbag = $bag['cardno'];
								$bag_id = $bag['id'];
								break;
							}
						}
					}
				}
				if($send_status){
					ActivityService::updateUserActivity($user_activity['id'],array('is_pickup'=>1));
					
					$res = ActivityService::sendAndroidMsg($user_activity['uid'],$send_giftbag,'users');
					if($res && $res['errorCode']==0){
						$bag_id && ActivityService::updateSendMessageStatue($bag_id);
					}
				}
			}
		}
	}
}