<?php
/**
 * @package Youxiduo
 * @category Base 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\User\H5;
use Youxiduo\Android\CreditService;
use Illuminate\Support\Facades\Config;
use Youxiduo\Base\BaseService;
use Youxiduo\User\Model\Account;
use Youxiduo\User\Model\UserMobile;
use Youxiduo\Helper\Utility;
use Youxiduo\Android\Model\CreditLevel;
use Youxiduo\Android\Model\CreditAccount;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Youxiduo\Chat\ChatService;

class AccountService extends BaseService
{
	public static function getUserinfo($uid){
		$fields = array('uid','avatar','nickname','summary','sex','birthday','mobile','email');
		$user = DB::table('account')->select($fields)->where('uid', $uid)->first();
		if($user){
		$user['birthday'] = date('Y-m-d',$user['birthday']);
			$credit = CreditAccount::getUserCreditByUid($uid);
			$user['money'] = $credit[$uid]['money'];
			$user['experience'] = $credit[$uid]['experience'];
			$level = CreditLevel::getUserLevel($user['experience']);
			if($level){
				$user['level_name'] = $level['name'];
				$user['level_max'] = $level['end'];
			}else{
				$user['level_name'] = '';
				$user['level_max'] = '';
			}
		}
		return $user;
	}
	/**
	 * 返回好友的列表
	 * Enter description here ...
	 * @param unknown_type $uid
	 */
	public static function getUserlist($uid,$page){
		$result = ChatService::registerHx($uid);
		$uuid = $result['result'];
		$result = ChatService::getFriends($uuid);
		$userinfos = array();
		foreach($result['result'] as $v){
			$userinfos[] = self::getUserinfo($v);
		}
		$userinfo = array_chunk($userinfos,10,true);
		$key = $page - 1;
		$num = count($userinfo);
		if($key<$num){
			$userinfo = $userinfo[$key];
		}else{
			$userinfo = array();
		}
		
		return $userinfo;
	}
	/**
	 * 添加好友并返回好友列表页面
	 */
	public static function getAddfriend($uid, $fuid,$pageIndex,$pagesize,$page){
		$result = ChatService::registerHx($uid);
		$uuid = $result['result'];
		$fresult = ChatService::registerHx($fuid);
		$fuuid = $fresult['result'];
		$res = ChatService::getAddfriends($uuid,$fuuid);
		if($res['errorCode']==0){
			$data = self::getUserlist($uid,$page);
		}else{
			return $data = array();
		}
		return $data;
	}
	
	/**
	 * 随机返回一些用户的列表
	 * Enter description here ...
	 * @param unknown_type $uid
	 */
	public static function getSomeuserlist($page){
		$fields = array('uid','avatar','nickname','summary','sex','birthday','mobile','email');
		$start = ($page-1)*10+1;
		$end = 10;
		$res = DB::table('account')->select($fields)->orderBy('uid','desc')->skip($start)->take($end)->get();
		return $res;
	}
	/**
	 * 返回用户的聊天记录
	 */
	public static function getChatmessages($uid){
		$res = array(
			array('avatar'=>'images/avanta.png','nickname'=>'Lina','message'=>'What\'s your name?','fromid'=>'1'),
			array('avatar'=>'images/avanta.png','nickname'=>'Lina','message'=>'What\'s your name?','toid'=>'1'),
			array('avatar'=>'images/avanta.png','nickname'=>'Lina','message'=>'What\'s your name?','fromid'=>'1'),
			array('avatar'=>'images/avanta.png','nickname'=>'Lina','message'=>'What\'s your name?','toid'=>'1'),
		);
		return $res;
	}
	/**
	 * 更新用户的信息
	 */
	public static function updateUserinfo($userinfo,$uid){
		$res = DB::table('account')->where('uid',$uid)->update($userinfo);
		//$res = DB::table('account')->where('uid',2)->first();
		return $res;
	}
	
}