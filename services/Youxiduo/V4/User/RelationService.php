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

use Youxiduo\V4\User\Model\Relation;

class RelationService extends BaseService
{
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
		$result = Relation::getAttentionList($uid,$pageIndex,$pageSize);
		return $result;
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
		$result = Relation::getFansList($uid,$pageIndex,$pageSize);
		return $result;
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
		$result = Relation::getFriendList($uid,$pageIndex,$pageSize);
		return $result;
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
		return Relation::addAttention($uid, $fuid);
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
		return Relation::removeAttention($uid, $fuid);
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
		return Relation::isAttention($uid, $fuid);
	}
}