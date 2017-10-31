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
namespace Youxiduo\V4\User;

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

use Youxiduo\V4\User\Model\Like;

class LikeService extends BaseService
{
	const ERROR_PARAMS_MISS = 'params_miss';
	const ERROR_LIKE_NOT_DATA = 'like_not_data';
	
	public static function doLike($uid,$target_id,$target_table)
	{
		if(!$uid || !$target_id || !$target_table) return self::ERROR_PARAMS_MISS;
		$exists = Like::isExists($uid, $target_id, $target_table);
		if($exists) return false;
		return Like::doLike($uid, $target_id, $target_table);
	}
	
	public static function unDoLike($uid,$target_id,$target_table)
	{
		if(!$uid || !$target_id || !$target_table) return self::ERROR_PARAMS_MISS;
		$exists = Like::isExists($uid, $target_id, $target_table);
		if(!$exists) return false;
		return Like::unDoLike($uid, $target_id, $target_table);
	}
	
	public static function isLike($uid,$target_id,$target_table)
	{
		$exists = Like::isExists($uid, $target_id, $target_table);
		return $exists;
	}
	
	public static function getLikeCount($target_id,$target_table)
	{
		if(!$target_id || !$target_table) return self::ERROR_PARAMS_MISS;
		return Like::getLikeCount($target_id, $target_table);
	}
	
	public static function getLikeList($target_id,$target_table,$pageIndex=1,$pageSize=10,$uid=0)
	{
		if(!$target_id || !$target_table) return self::ERROR_PARAMS_MISS;
		$uids = Like::getLikeList($target_id, $target_table,$pageIndex,$pageSize);
		if(!$uids) return array();//return self::ERROR_LIKE_NOT_DATA;
		$users = UserService::getMultiUserInfoByUids($uids,'short',$uid);
		if(!is_array($users)) $users = array();//return self::ERROR_LIKE_NOT_DATA;
		$out = array();
		$out_users = array();
		foreach($users as $user){
			$out_users[$user['uid']] = $user;
		}
		foreach($uids as $uid){
			if(!isset($out_users[$uid])) continue;
			$out[] = $out_users[$uid];
		}
		return $out;
	}
	
    public static function getLikeCountByTids($target_ids,$target_table)
	{
		if(!$target_ids || !$target_table) return self::ERROR_PARAMS_MISS;
		return Like::getLikeCountByTids($target_ids, $target_table);
	}
	
	public static function getLikeListByTids($target_ids,$target_table,$uid=0)
	{
		if(!$target_ids || !$target_table) return self::ERROR_PARAMS_MISS;
		$result = Like::getLikeListByTids($target_ids, $target_table);
		$uids = array();
		foreach($result as $row){
			$uids = array_merge($uids,$row);
		}
		if(!$uids) return array();//return self::ERROR_LIKE_NOT_DATA;
		$users = UserService::getMultiUserInfoByUids($uids,'short',$uid);
		if(!is_array($users)) return self::ERROR_LIKE_NOT_DATA;
		$out = array();
		$out_users = array();
		foreach($users as $user){
			$out_users[$user['uid']] = $user;
		}
		
		foreach($result as $target_id=>$uuids){
			foreach($uuids as $uid){
				if(!isset($out_users[$uid])) continue;
				$out[$target_id][] = $out_users[$uid];
			}
		}
		return $out;
	}
}