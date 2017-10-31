<?php
namespace modules\feedback\models;
use Yxd\Modules\Core\BaseModel;
use Yxd\Services\UserService;
use Yxd\Models\YxdHelper;

class ChatModel extends BaseModel
{
	/**
	 * 反馈-用户列表
	 */
	public static function getChatUserList($to_uid,$search,$page=1,$size=10)
	{
		$total = self::buildSearch($to_uid, $search)->count();
		$chat_users = self::buildSearch($to_uid, $search)->forPage($page,$size)->orderBy('last_time','desc')->get();
		$uids = array();
		foreach($chat_users as $row){
			$uids[] = $row['from_uid'];
		}
		$users = UserService::getBatchUserInfo($uids);
		foreach($chat_users as $key=>$row){
			if(!isset($users[$row['from_uid']])) continue;
			$row['from_user'] = $users[$row['from_uid']];
			$row['last_message'] = json_decode($row['last_message'],true)===null ? $row['last_message'] : json_decode($row['last_message'],true); 
			$chat_users[$key] = $row;
		}
		return array('result'=>$chat_users,'total'=>$total);
	}
	
	protected static function buildSearch($to_uid,$search)
	{
		$tb = self::dbClubSlave()->table('chat_user')->where('to_uid','=',$to_uid);
		if(isset($search['keytype']) && isset($search['keyword'])){
			if($search['keytype']=='uid'){
				$tb = $tb->where('from_uid','=',(int)$search['keyword']);
			}elseif($search['keytype']=='nickname'){
				$uids = self::dbClubSlave()->table('account')->select('uid','nickname')->where('nickname','like','%'.$search['keyword'].'%')->lists('uid');
				if($uids){
				    $tb = $tb->whereIn('from_uid',$uids);
				}
			}
		}
		return $tb;
	}
	
	/**
	 * 反馈信息列表
	 */
	public static function getChatList($from_uid,$to_uid,$page=1,$size=10)
	{
		$total = self::buildChatList($from_uid, $to_uid)->count();
		$chat_logs = self::buildChatList($from_uid, $to_uid)->orderBy('addtime','desc')->forPage($page,$size)->get();
		
		$uids = array($from_uid,$to_uid);
		$users = UserService::getBatchUserInfo($uids);
	    foreach($chat_logs as $key=>$row){
			$row['from_user'] = $row['from_uid']==1 ? YxdHelper::getHelper() : $users[$row['from_uid']];
			$row['to_user'] = $row['to_uid']==1 ? YxdHelper::getHelper() : $users[$row['to_uid']];
			$row['message'] = json_decode($row['message'],true)===null ? $row['message'] : json_decode($row['message'],true); 
			$chat_logs[$key] = $row;
		}
		//krsort($chat_logs);
		//var_dump($chat_logs);exit;
		return array('result'=>$chat_logs,'total'=>$total);
	}
	
	protected static function buildChatList($from_uid,$to_uid)
	{
		return self::dbClubSlave()->table('chat_log')->where(function($query)use($from_uid,$to_uid){
		    return $query = $query->where('from_uid','=',$to_uid)->where('to_uid','=',$from_uid);
		})->orWhere(function($query)use($from_uid,$to_uid){
		    return $query = $query->where('from_uid','=',$from_uid)->where('to_uid','=',$to_uid);
		});
		
	}
}