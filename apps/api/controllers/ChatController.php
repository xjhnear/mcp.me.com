<?php

use Yxd\Services\UserService;
use Yxd\Services\ChatService;
use Yxd\Services\ShoppingService;
use Yxd\Services\TaskService;
use Yxd\Services\TopicService;
use Yxd\Models\YxdHelper;
use Illuminate\Support\Facades\Input;

class ChatController extends BaseController
{
	/**
	 * 添加会话
	 */
	public function addUser()
	{
		$from = Input::get('from');
		$to = Input::get('to');
		$result = true;//ChatService::addChatUser($from,$to);
		if($result==true){
			return $this->success(array('result'=>array('to_uid'=>$to)));
		}
	}
	
	/**
	 * 会话用户列表
	 */
	public function users()
	{
		$uid = Input::get('uid');
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',20);
		
		$result = ChatService::getChatUserList($uid,$page,$pagesize);
		$out = array();
		foreach($result['users'] as $user){
			$chat['fromUid'] = $user['from_user']['uid'];
			$chat['fromUserLevel'] = UserService::joinImgUrl($user['from_user']['level_icon']);
			$chat['nick'] = $user['from_user']['nickname'];
			$chat['avatar'] = ChatService::joinImgUrl($user['from_user']['avatar']);
			$chat['words'] = json_decode($user['last_message'],true)===null ? $user['last_message'] : json_decode($user['last_message'],true);
			$chat['addTime'] = $user['last_time'] ? date('Y-m-d H:i:s',$user['last_time']) : '';
			$chat['badege'] = ChatService::isReadChatMsg($uid,$user['from_uid']);
			$out[] = $chat;
		}
		return $this->success(array('result'=>$out,'totalCount'=>$result['total']));
	}
	
	/**
	 * 聊天记录
	 */
	public function detail()
	{
		$uid = Input::get('uid');
		$mid = Input::get('targetID');
		$type = Input::get('type');
		if($type==0) $mid = YxdHelper::Id();
		return $this->records($uid, $mid);
	}
	
	/**
	 * 删除聊天记录
	 * 3.1版新增接口
	 */
	public function delete()
	{
		$uid = Input::get('uid');
		$mid = Input::get('targetID');
		ChatService::deleteChatUser($mid,$uid);
		return $this->success(array('result'=>array()));
	}
	
	/**
	 * 
	 */
	public function records($uid,$mid)
	{
		$out = array();
		$result = ChatService::getChatRecord($uid,$mid,1,10000);
		foreach($result['records'] as $row){
			$chat = array();
			$chat['mid'] = $row['id'];
			//
			$chat['content'] = json_decode($row['message'],true)===null ? $row['message'] : json_decode($row['message'],true);
			$chat['imageURL'] = ChatService::joinImgUrl($row['pic']);
			if($row['pic'] && file_exists(storage_path() . $row['pic'])){
				list($width,$height,$type,$ext) = getimagesize(storage_path() . $row['pic']);
				$chat['imgWidth'] = $width;
				$chat['imgHeight'] = $height;
				$chat['contentType'] = 2;
			}else{
				$chat['imgWidth'] = 0;
				$chat['imgHeight'] = 0;
				$chat['contentType'] = 1;
			}
			$chat['time'] = date('Y-m-d H:i:s',$row['addtime']);
		    if($row['from_user']['uid'] == YxdHelper::Id()){
				$row['from_user'] = YxdHelper::getHelper();
			}
			$chat['fromUser']['userID'] = $row['from_user']['uid'];
			$chat['fromUser']['userName'] = $row['from_user']['nickname'];
			$chat['fromUser']['userAvator'] = ChatService::joinImgUrl($row['from_user']['avatar']);
			$chat['fromUser']['userLevel'] = $row['from_user']['level_name'];
			$chat['fromUser']['userLevelImage'] = ChatService::joinImgUrl($row['from_user']['level_icon']);
			if($row['to_user']['uid'] == YxdHelper::Id()){
				$row['to_user'] = YxdHelper::getHelper();
			}
			$chat['toUser']['userID'] = $row['to_user']['uid'];
			$chat['toUser']['userName'] = $row['to_user']['nickname'];
			$chat['toUser']['userAvator'] = ChatService::joinImgUrl($row['to_user']['avatar']);
			$chat['toUser']['userLevel'] = $row['to_user']['level_name'];
			$chat['toUser']['userLevelImage'] = ChatService::joinImgUrl($row['to_user']['level_icon']);
			
			$out[] = $chat;
		}
		return $this->success(array('result'=>$out,'totalCount'=>$result['total']));
	}
		
	/**
	 * 发送消息
	 */
	public function sendMessage()
	{
		$from = (int)Input::get('fromUserID');
		$to = (int)Input::get('toUserID');
		$message = Input::get('content','');
	    $file = TopicService::doUpload('image',null,$from);
		if($file['status']==200){
			$pic = $file['data']['attachment'];
		}else{
			$pic = '';
		}
		//信息反馈
		if($to <= 0){
			$to = YxdHelper::Id();
		}
		$result = ChatService::sendChatMessage($from,$to,json_encode($message),$pic);
		if($result>0){
			$msg = ChatService::getChatMessage($result);
			$msg['from_user'] = UserService::getUserInfo($msg['from_uid']);
			$msg['to_user'] = UserService::getUserInfo($msg['to_uid']);
			$chat = array();
			$chat['mid'] = $msg['id'];
			//
			$chat['content'] = json_decode($msg['message'],true)===null ? $msg['message'] : json_decode($msg['message'],true);
			$chat['imageURL'] = ChatService::joinImgUrl($msg['pic']);
			if($msg['pic'] && file_exists(storage_path() . $msg['pic'])){
				list($width,$height,$type,$ext) = getimagesize(storage_path() . $msg['pic']);
				$chat['imgWidth'] = $width;
				$chat['imgHeight'] = $height;
				$chat['contentType'] = 2;
			}else{
				$chat['imgWidth'] = 0;
				$chat['imgHeight'] = 0;
				$chat['contentType'] = 1;
			}
			$chat['time'] = date('Y-m-d H:i:s',$msg['addtime']);
			$chat['fromUser']['userID'] = $msg['from_user']['uid'];
			$chat['fromUser']['userName'] = $msg['from_user']['nickname'];
			$chat['fromUser']['userAvator'] = ChatService::joinImgUrl($msg['from_user']['avatar']);
			$chat['fromUser']['userLevel'] = $msg['from_user']['level_name'];
			$chat['fromUser']['userLevelImage'] = ChatService::joinImgUrl($msg['from_user']['level_icon']);
		    if($to == YxdHelper::Id()){
				$msg['to_user'] = YxdHelper::getHelper();
			}
			$chat['toUser']['userID'] = $msg['to_user']['uid'];
			$chat['toUser']['userName'] = $msg['to_user']['nickname'];
			$chat['toUser']['userAvator'] = ChatService::joinImgUrl($msg['to_user']['avatar']);
			$chat['toUser']['userLevel'] = $msg['to_user']['level_name'];
			$chat['toUser']['userLevelImage'] = ChatService::joinImgUrl($msg['to_user']['level_icon']);
			
			return $this->success(array('result'=>$chat));
		}
	}
	
	/**
	 * 通知
	 */
	public function notice()
	{
		$uid = Input::get('uid');
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',20);
		$last = Input::get('lastdate');
		$result = ChatService::systemNotice($uid,$last,$page,$pagesize);
		$out = array();
		foreach($result['notice'] as $row){
			$notice = array();
			$notice['mid'] = YxdHelper::Id();
			$notice['nick'] = YxdHelper::NickName();
			$notice['avatar'] = YxdHelper::Avatar();
			$notice['level'] = YxdHelper::LevelName();
			$notice['systemInfo'] = $row['message'];
			$notice['addTime'] = date('Y-m-d H:i:s',$row['addtime']);
			$out[] = $notice;
		}
		return $this->success(array('result'=>$out,'totalCount'=>$result['total']));
	}
}