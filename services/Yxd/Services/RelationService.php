<?php
namespace Yxd\Services;

use Yxd\Models\Forum;

use Yxd\Models\Follow;
use Yxd\Models\Friend;

/**
 * 关系
 */
class RelationService extends Service
{
	/**
	 * 添加好友
	 * @deprecated
	 */
	public static function addFriend($uid,$fuid,$group_id=0)
	{
		$id = Friend::addFriend($uid, $fuid, $group_id);
		if($id==-1){
			return self::send(500);
		}else{
			return self::send(200,true);
		}
	}
	
	/**
	 * 是否是好友
	 * @deprecated
	 */
	public static function isFriend($uid,$fuid)
	{
		return Friend::isFriend($uid, $fuid);
	}
	
	/**
	 * 删除好友
	 * @deprecated
	 */
	public static function deleteFriend($uid,$fuid)
	{
	    $id = Friend::deleteFriend($uid, $fuid);
		if($id==-1){
			return self::send(500);
		}else{
			return self::send(200,true);
		}
	}
	
	/**
	 * 获取好友列表
	 * @deprecated
	 */
	public static function getFriendList($uid,$page=1,$pagesize=10)
	{
	    $uids = Friend::getFriendList($uid,$page,$pagesize);
		if(!$uids){
			return array('total'=>0,'users'=>array());
		}else{
			$total = Friend::getFriendCount($uid);
			$users = UserService::getBatchUserInfo($uids);
			return array('total'=>$total,'users'=>$users);
		}
	}
	
	/**
	 * 添加关注
	 */
	public static function addFollow($uid,$fuid)
	{
		$id = Follow::addFollow($uid, $fuid);
	    if($id==-1){
			return self::send(500);
		}else{
			//self::addUserFollowsCache($uid, array($fuid));
			//self::addUserFollowersCache($fuid,array($uid));
			return self::send(200,true);
		}
	}
	
	/**
	 * 是否已经关注
	 */
	public static function isFollow($uid,$fuid)
	{
		return Follow::isFollow($uid, $fuid);
	}
	
	/**
	 * 删除关注
	 */
    public static function deleteFollow($uid,$fuid)
	{
		$id = Follow::deleteFollow($uid, $fuid);
		//self::deleteUserFollowsCache($uid, array($fuid));
		//self::deleteUserFollowersCache($fuid,array($uid));
	    if($id==-1){
			return self::send(500);
		}else{
			
			return self::send(200,true);
		}
	}
	
	/**
	 * 获取关注列表
	 */
    public static function getFollowList($uid,$page=1,$pagesize=10)
	{
		//return self::getUserFollowsCache($uid,$page,$pagesize);
	    $uids = Follow::getFollowList($uid,$page,$pagesize);
		if(!$uids){
			return array('total'=>0,'users'=>array());
		}else{
			$total = Follow::getFollowCount($uid);
			$users = UserService::getBatchUserInfo($uids);
			
			return array('total'=>$total,'users'=>$users);
		}
	}
	
	public static function getFollowUids($uid)
	{
		$uids = Follow::getFollowList($uid,1,2000);
		return $uids ? $uids : array();
	}
	
	/**
	 * 获取粉丝列表
	 */
    public static function getFollowerList($uid,$page=1,$pagesize=10)
	{
		//return self::getUserFollowersCache($uid,$page,$pagesize);
	    $uids = Follow::getFollowerList($uid,$page,$pagesize);
		if(!$uids){
			return array('total'=>0,'users'=>array());
		}else{
			$total = Follow::getFollowerCount($uid);
			$users = UserService::getBatchUserInfo($uids);
			return array('total'=>$total,'users'=>$users);
		}
	}
	
	/**
	 * 加入圈子
	 * @param int $gid
	 * @param int $uid
	 */
	public static function addCircleUser($gid,$uid)
	{
		$success = Forum::addCircleUser($gid, $uid);
		if($success){
			self::addCircleUserCache($gid, $uid);
			return true;
		}
		return $success;
	}
	
    /**
	 * 加入圈子
	 * @param int $gid
	 * @param int|array $uid
	 */
	protected static function addCircleUserCache($gid,$uid)
	{
		$key = 'circle:' . $gid . ':users';
		return self::redis()->sadd($key,$uid);
	}
	
	/**
	 * 是否已经加入圈子
	 */
	public static function isExistsCircleUser($gid,$uid)
	{
		return Forum::isExistsCircleUser($gid, $uid);
	}
	
	/**
	 * 是否已经加入圈子
	 */
	protected static function isExistsCircleUserCache($gid,$uid)
	{
		$key = 'circle:' . $gid . ':users';
		return self::redis()->sismember($key,$uid);
	}
	
	/**
	 * 获取某圈子用户列表
	 * @param int $gid
	 * @param int $page
	 * @param int $pagesize 
	 */
	public static function getCircleUserList($gid,$page=1,$pagesize=20)
	{
		$data = self::getCircleUserListCache($gid,$page,$pagesize);
		if($data){
			return $data;
		}
		$total = Forum::getCircleUserCount($gid);
		$uids  = Forum::getCircleUsers($gid,$page,$pagesize);
		$users = UserService::getBatchUserInfo($uids);
		$data = array('total'=>$total,'users'=>$users);
		return $data;
	}
	
    /**
	 * 获取某圈子用户列表
	 * @param int $gid
	 * @param int $page
	 * @param int $pagesize 
	 */
	protected static function getCircleUserListCache($gid,$page=1,$pagesize=20)
	{
		$key = 'circle:' . $gid . ':users';
		$total = self::redis()->scard($key);
		if($total==0) return null;
		$start = ($page-1) * $pagesize;
		$all  = self::redis()->smembers($key);
		$uids = array_slice($all,$start,$pagesize);
		$users = UserService::getBatchUserInfo($uids);
		$data = array('total'=>$total,'users'=>$users);
		return $data;
	}
	
	/**
	 * 退出圈子
	 * @param int $gid
	 * @param int $uid
	 */
	public static function deleteCircleUser($gid,$uid)
	{
		$success = Forum::deleteCircleUser($gid, $uid);
		if($success){
			self::deleteCircleUserCache($gid, $uid);
			return true;
		}
		return $success;
	}
	
    /**
	 * 退出圈子
	 * @param int $gid
	 * @param int|array $uid
	 */
	protected static function deleteCircleUserCache($gid,$uid)
	{
		$key = 'circle:' . $gid . ':users';
		return self::redis()->srem($key,$uid);
	}
	
    /**
	 * 从Redis中获取好友信息列表
	 */
	protected static function getUserFriendsCache($uid)
	{
		//$prefix = substr($uid,-1);
		//$key = 'user:table-' . $prefix . ':friend';
		$key = 'user:' . $uid . ':friend';
		
		$friend_uids = self::redis()->smembers($key); 
		if($friend_uids){
			return self::getUserInfoCache($friend_uids);
		}
		return null;
	}
	
    /**
	 * 从Redis中获取共同的好友
	 */
	protected static function getUserCommonFriendsCache($uid,$target_uid)
	{
		$key_1 = 'user:' . $uid . ':friend';
		$key_2 = 'user:' . $target_uid . ':friend';
		
		$uids = self::redis()->sinter($key_1,$key_2);
		if($uids){
			return self::getUserInfoCache($uids);
		}else{
			return null;
		}
	}
	
	/**
	 * 更新Redis中用户好友
	 * @param int $uid
	 * @param int|array $fuids
	 */
	protected static function addUserFriendsCache($uid,$fuids)
	{
		$key = 'user:' . $uid . ':friend';
		return self::redis()->sadd($key,$fuids);
	}
	
	/**
	 * 更新Redis中用户好友
	 * @param int $uid
	 * @param int|array $fuids
	 */
	protected static function deleteUserFriendsCache($uid,$fuids)
	{
		$key = 'user:' . $uid . ':friend';
		return self::redis()->srem($key,$fuids);
	}
	
	
    /**
	 * 从Redis中获取关注人信息列表
	 */
	protected static function getUserFollowsCache($uid,$page=1,$size=10)
	{
		$key = 'user:' . $uid . ':follow';		
		$follow_uids = self::redis()->smembers($key);
		if(!$follow_uids){
			return array('total'=>0,'users'=>array());
		}else{
			$total = count($follow_uids);
			$pages = array_chunk($follow_uids,$size);
			$uids = isset($pages[$page-1]) ? $pages[$page-1] : array(); 
			$users = UserService::getBatchUserInfo($uids);
			
			return array('total'=>$total,'users'=>$users);
		}  
	}
	
    /**
	 * 从Redis中获取共同的关注者
	 */
	protected static function getUserCommonFollowsCache($uid,$target_uid)
	{
		$key_1 = 'user:' . $uid . ':follow';
		$key_2 = 'user:' . $target_uid . ':follow';
		
		$uids = self::redis()->sinter($key_1,$key_2);
		if($uids){
			return self::getUserInfoCache($uids);
		}else{
			return null;
		}
	}
	
	/**
	 * 更新Redis中用户关注
	 * @param int $uid
	 * @param int|array $fuids
	 */
	protected static function addUserFollowsCache($uid,$fuids)
	{
		$key = 'user:' . $uid . ':follow';
		return self::redis()->sadd($key,$fuids);
	}
	
	/**
	 * 更新Redis中用户关注
	 * @param int $uid
	 * @param int|array $fuids
	 */
	protected static function deleteUserFollowsCache($uid,$fuids)
	{
		$key = 'user:' . $uid . ':follow';
		return self::redis()->srem($key,$fuids);
	}
	
	
    /**
	 * 从Redis中获取粉丝信息列表
	 */
	protected static function getUserFollowersCache($uid,$page=1,$size=10)
	{
		$key = 'user:' . $uid . ':follower';		
		$follower_uids = self::redis()->smembers($key);
	    if(!$follower_uids){
			return array('total'=>0,'users'=>array());
		}else{
			$total = count($follower_uids);
			$pages = array_chunk($follower_uids,$size);
			$uids = isset($pages[$page-1]) ? $pages[$page-1] : array(); 
			$users = UserService::getBatchUserInfo($uids);
			
			return array('total'=>$total,'users'=>$users);
		} 		
	}		
	
	/**
	 * 更新Redis中用户粉丝
	 * @param int $uid
	 * @param int|array $fuids
	 */
	protected static function addUserFollowersCache($uid,$fuids)
	{
		$key = 'user:' . $uid . ':follower';
		return self::redis()->sadd($key,$fuids);
	}
	
	/**
	 * 更新Redis中用户粉丝
	 * @param int $uid
	 * @param int|array $fuids
	 */
	protected static function deleteUserFollowersCache($uid,$fuids)
	{
		$key = 'user:' . $uid . ':follower';
		return self::redis()->srem($key,$fuids);
	}
}