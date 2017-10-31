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
namespace Youxiduo\Android\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
/**
 * 游戏礼包模型类
 */
final class GiftbagAppoint extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
    /**
	 * 获取礼包指定发放用户ID
	 * @param int $giftbag_id
	 * @return array
	 */
	public static function m_getGiftbagAppointUids($giftbag_id){
		if(!$giftbag_id) return array();
		return self::db()->where('giftbag_id',$giftbag_id)->lists('uid');
	}
	
	/**
	 * 更新礼包指定发放用户ID
	 * @param int $giftbag_id
	 * @param arr $uids
	 */
	public static function m_updateGiftbagAppointUids($giftbag_id,$data){
		$exception = self::dbClubMaster()->transaction(function() use ($giftbag_id,$data){
			//删除礼包原先指定的所有用户id
			GiftbagAppoint::db()->where('giftbag_id',$giftbag_id)->delete();
			GiftbagAppoint::db()->table('giftbag_appoint')->insert($data);
		});
		return is_null($exception) ? true : false;
	}
	
	/**
	 * 通过礼包和用户id获取指定信息（判断是否指定）
	 * @param unknown $giftbag_id
	 * @param unknown $uid
	 */
	public static function getGiftbagAppointInfoByGbidAndUid($giftbag_id,$uid){
		if(!$giftbag_id || $uid) return array();
		return self::db()->where('giftbag_id',$giftbag_id)->where('uid',$uid)->first();
	}
}