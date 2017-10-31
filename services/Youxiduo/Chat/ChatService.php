<?php
namespace Youxiduo\Chat;

use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use libraries\Helpers;
use Yxd\Services\UserService;
use Illuminate\Support\Facades\Config;

class ChatService extends BaseService{
	/**
	 * 环信注册/获取环信uuid
	 * @param string $uid
	 * @return boolean|mixed
	 */
	public static function registerHx($uid){
		if(!$uid) return false;
		$result = Utility::loadByHttp(Config::get('app.android_chat_api_url').'user-create',array('uid'=>$uid));
		return $result;
	}
	
	/**
	 * 获取游戏多id（单个）
	 * @param string $hxid
	 * @return boolean|mixed
	 */
	public static function getYxdUid($hxid){
		if(!$hxid) return false;
		$result = Utility::loadByHttp(Config::get('app.android_chat_api_url').'users',array('username'=>$hxid));
		return $result;
	}

	/**
	 * 获取用户的好友关系
	 * @param string $uuid
	 * @return boolean|mixed
	 */
	public static function getFriends($uuid){
		if(!$uuid) return false;
		$result = Utility::loadByHttp(Config::get('app.android_chat_api_url').'user-contacts',array('uuid'=>$uuid));
		return $result;
	}
	
	/**
	 * 获取两个人聊天记录
	 * @param string $fromUid 环信id
	 * @param string $toUid 环信id
	 * @param number $pageIndex
	 * @param number $pageSize
	 * @param number $viewTime
	 * @return mixed
	 */
	public static function getChatRecords($fromUid,$toUid,$pageIndex=1,$pageSize=10,$viewTime=0){
		if(!$fromUid || !$toUid) return false;
		$params = array(
				'fromUid' => $fromUid,
				'toUid' => $toUid,
				'pageIndex' => $pageIndex,
				'pageSize' => $pageSize
		);
		if($viewTime) $params['viewTime'] = $viewTime;
		$result = Utility::loadByHttp(Config::get('app.android_chat_api_url').'user-chatMessages',$params);
		return $result;
	}

	/**
	 * h5获取未读个人消息列表（非服务）
	 */
	public static function getUnreadPersonalMsg($uid){
		$uuid_res = ChatService::registerHx($uid);
		$personal_msg = array();
		if($uuid_res && !$uuid_res['errorCode']){
			$uuid = $uuid_res['result'];
			$friends = ChatService::getFriends($uuid);
			$tmp_data = array();
			if($friends && !$friends['errorCode']){
				foreach ($friends['result'] as $hxid=>$yxdid){
					$records = ChatService::getChatRecords($uuid, $hxid);
					$tmp_data[$yxdid] = $records;
				}
			}
			if($tmp_data){
				$fuid = array();
				foreach ($tmp_data as $key=>$val){
					if($val['result']) $fuid[] = $key;
				}
				$fuinfo = UserService::getBatchUserInfo(array_unique($fuid));
				if($fuinfo){
					foreach ($fuinfo as $uid=>$user){
						$bodies = current(json_decode($tmp_data[$uid]['result'][0]['bodies'],true));
						if($bodies['type'] == 'txt'){
							$last_msg = $bodies['msg'];
						}elseif ($bodies['type'] == 'img'){
							$last_msg = '[图片]';
						}else{
							$last_msg = '[语音]';
						}
						$personal_msg[] = array(
								'uid' => $uid,
								'uname' => $user['nickname'],
								'avatar' => $user['avatar'],
								'last_msg' => $last_msg,
								'time_ago' => Helpers::smarty_modifier_time_ago(strtotime(date("Y-m-d H:i:s",substr($tmp_data[$uid]['result'][0]['timestamp'],0,10))))
						);
					}
				}
			}
		}
		return $personal_msg;
	}
	/**
	 * 添加好友
	 */
	public static function getAddfriend($uid,$toid){
		if(!$uid || !$toid) return false;
		$result = Utility::loadByHttp(Config::get('app.android_chat_api_url').'add-friend',array('ownerId'=>$uid,'friendId'=>$toid));
		return $result;
	}
}