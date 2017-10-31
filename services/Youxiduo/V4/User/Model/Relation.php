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
namespace Youxiduo\V4\User\Model;

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

use Youxiduo\Helper\Utility;
/**
 * 关系模型类
 */
final class Relation extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
    /**
	 * 关注列表
	 * 
	 * @param int $uid 用户UID
	 * 
	 * @return array|null 
	 * 
	 */
	public static function getAttentionList($uid,$pageIndex=1,$pageSize=10)
	{
		$result = self::db()->where('uid','=',$uid)->forPage($pageIndex,$pageSize)->get();
		if(!$result) return array();
		$out = array();
		foreach($result as $row){
			$out[] = array('uid'=>$row['fuid']);
		}
		return $out;
	}
	
    /**
	 * 粉丝列表
	 * 
	 * @param int $uid 用户UID
	 * 
	 * @return array|null 
	 * 
	 */
	public static function getFansList($uid,$pageIndex=1,$pageSize=10)
	{
		$result = self::db()->where('fuid','=',$uid)->forPage($pageIndex,$pageSize)->get();
		if(!$result) return array();
		$out = array();
		foreach($result as $row){
			$out[] = array('uid'=>$row['uid']);
		}
		return $out;
	}
	
    /**
	 * 好友列表
	 * 
	 * @param int $uid 用户UID
	 * 
	 * @return array|null 
	 * 
	 */
	public static function getFriendList($uid,$pageIndex=1,$pageSize=10)
	{
		$user_att = self::db()->where('uid','=',$uid)->lists('fuid');
		$user_fans = self::db()->where('fuid','=',$uid)->lists('uid');
		$user_friend = array_intersect($user_att,$user_fans);
		$out = array();
		if($user_friend) $user_friend = array_unique($user_friend);
		foreach($user_friend as $row){
			$out[] = array('uid'=>$row);
		}
		return $out;
	}
	
	/**
	 * 添加关注
	 * 
	 * @param int $uid 用户UID
	 * @param int $fuid 关注的用户UID
	 * 
	 * @return bool 成功返回true,失败返回false
	 */
	public static function addAttention($uid,$fuid)
	{
		$data['uid'] = $uid;
		$data['fuid'] = $fuid;
		$data['ctime'] = (int)microtime(true);
		$id = self::db()->insertGetId($data);
		return $id ? true : false;
	}
	
    /**
	 * 取消关注
	 * 
	 * @param int $uid 用户UID
	 * @param int $fuid 关注的用户UID
	 * 
	 * @return bool 成功返回true,失败返回false
	 */
	public static function removeAttention($uid,$fuid)
	{
		$count = self::db()->where('uid','=',$uid)->where('fuid','=',$fuid)->delete();
		return $count>0 ? true : false;
	}
	
    /**
	 * 是否关注
	 * 
	 * @param int $uid 用户UID
	 * @param int $fuid 关注的用户UID
	 * 
	 * @return bool 成功返回true,失败返回false
	 */
	public static function isAttention($uid,$fuid)
	{
		$count = self::db()->where('uid','=',$uid)->where('fuid','=',$fuid)->count();
		return $count>0 ? true : false;
	}
	
	public static function getAllAttention($uid)
	{
		return self::db()->where('uid','=',$uid)->lists('fuid');
	}
	
    public static function getAllFans($uid)
	{
		return self::db()->where('fuid','=',$uid)->lists('uid');
	}
}