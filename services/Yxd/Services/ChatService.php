<?php
namespace Yxd\Services;

use Illuminate\Support\Facades\Event;

use Yxd\Services\Models\ChatLog;
use Yxd\Services\Models\ChatUser;

class ChatService extends Service
{
	/**
	 * 添加聊天会话
	 */
	public static function addChatUser($from_uid,$to_uid)
	{
		$from = ChatUser::db()->where('from_uid','=',$from_uid)->where('to_uid','=',$to_uid)->count();
		$to = ChatUser::db()->where('from_uid','=',$to_uid)->where('to_uid','=',$from_uid)->count();
		$data = array();
		if($from==0){
			$data[] = array('from_uid'=>$from_uid,'to_uid'=>$to_uid,'last_message'=>'','last_time'=>time());
		}
		if($to==0){
			$data[] = array('from_uid'=>$to_uid,'to_uid'=>$from_uid,'last_message'=>'','last_time'=>time());
		}
		if($data){
		    ChatUser::db()->insert($data);
		}
		return true;
	}
	
	/**
	 * 删除会话及聊天记录
	 */
	public static function deleteChatUser($from_uid,$to_uid)
	{
		$rows = ChatUser::db()->where('from_uid','=',$from_uid)->where('to_uid','=',$to_uid)->delete();
		$key = 'message::chat::uid::' . $to_uid;
		self::redis()->srem($key,$from_uid);
		
		ChatLog::db()->where('from_uid','=',$from_uid)->where('to_uid','=',$to_uid)->update(array('from_isdel'=>1));
		ChatLog::db()->where('from_uid','=',$to_uid)->where('to_uid','=',$from_uid)->update(array('to_isdel'=>1));
		
		return $rows>0 ? true : false;
	}
	
	/**
	 * 获取用户会话列表
	 */
	public static function getChatUserList($uid,$page=1,$pagesize=20)
	{
		$chat_users = ChatUser::db()->where('to_uid','=',$uid)->orderBy('last_time','desc')->forPage($page,$pagesize)->get();
		$total = ChatUser::db()->where('to_uid','=',$uid)->count();
		$uids = array();
		foreach($chat_users as $row){
			$uids[] = $row['from_uid'];
		}
		$uids[] = $uid;
		$users = UserService::getBatchUserInfo($uids);
		foreach($chat_users as $index=>$user){
			$chat_users[$index]['from_user'] = $users[$user['from_uid']];
			$chat_users[$index]['to_user'] = $users[$user['to_uid']]; 
		}
		return array('users'=>$chat_users,'total'=>$total);
	}
	
	/**
	 * 获取聊天记录
	 */
	public static function getChatRecord($from,$to,$page=1,$pagesize=20)
	{
		self::resetChatMsgNum($from, $to);
	    $chat_log = self::dbClubSlave()->select('select * from yxd_chat_log where (from_uid=? and to_uid=? and to_isdel=0) or (from_uid=? and to_uid=? and from_isdel=0) order by id asc',array($from,$to,$to,$from));
	    //$total = ChatLog::db()->where('to_uid','=',$from)->orWhere('from_uid','=',$from)->count();
	    $total = count($chat_log);
		$uids = array();		
		$uids[] = $from;
		$uids[] = $to;		
		$users = UserService::getBatchUserInfo(array_unique($uids));
		foreach($chat_log as $index=>$user){
			$chat_log[$index]['from_user'] = $users[$user['from_uid']];
			$chat_log[$index]['to_user'] = $users[$user['to_uid']]; 
		}
		return array('records'=>$chat_log,'total'=>$total);
	}
	
	public static function resetChatMsgNum($uid,$to_uid)
	{
		$key = 'message::chat::uid::' . $uid;
		self::redis()->srem($key,$to_uid);
	}
	
	public static function isReadChatMsg($uid,$to_uid)
	{
		$key = 'message::chat::uid::' . $uid;
		$all = self::redis()->smembers($key);
		if($all && is_array($all)){
			return in_array($to_uid,$all) ? 1 : 0;
		}
		return 0;
	}
	
	public static function getNotReadChatMsgNum($uid)
	{
		$key = 'message::chat::uid::' . $uid;
		$all = self::redis()->smembers($key);
		return is_array($all) ? count($all) : 0;
	}
	
	public static function addNotReadChatMsgNum($from,$to)
	{
		if($from<=1) return false;
		$key = 'message::chat::uid::' . $to;
		self::redis()->sadd($key,$from);
		return true;
	}
	
	/**
	 * 发送聊天内容
	 */
	public static function sendChatMessage($from_uid,$to_uid,$message,$pic='')
	{
		$to_uid!=1 && self::addChatUser($from_uid,$to_uid) && self::addNotReadChatMsgNum($from_uid, $to_uid);
		
		$data = array('from_uid'=>$from_uid,'to_uid'=>$to_uid,'message'=>$message,'pic'=>$pic,'addtime'=>time());
		    //array('from_uid'=>$to_uid,'to_uid'=>$from_uid,'message'=>$message,'addtime'=>time()),		
		$id = ChatLog::db()->insertGetId($data);
		if($pic) $message = '[图片]';
		ChatUser::db()->where('from_uid','=',$from_uid)->where('to_uid','=',$to_uid)->update(array('last_message'=>$message,'last_time'=>time()));
		ChatUser::db()->where('from_uid','=',$to_uid)->where('to_uid','=',$from_uid)->update(array('last_message'=>$message,'last_time'=>time()));
		return $id;
	}
	
	public static function getChatMessage($id)
	{
		return ChatLog::db()->where('id','=',$id)->first();
	}
	
	/**
	 * 获取系统通知
	 * @deprecated
	 */
	public static function systemNotice($uid,$last=null,$page=1,$pagesize=10)
	{
		if(!$last){
			$last = time();
		}else{
			$last = strtotime($last);
		}
		
		$tb = self::dbCmsSlave()->table('send_message')->where('addtime','>=',$last);
		$total = $tb->count();
		$notice = $tb->forPage($page,$pagesize)->orderBy('addtime','desc')->get(); 
		return array('notice'=>$notice,'total'=>$total);
	}
	
	public static function getNotReadFeedbackNum($uid,$last)
	{
		return ChatLog::db()->where('to_uid','=',$uid)->where('from_uid','=',1)->where('addtime','>=',$last)->count();
	}
}