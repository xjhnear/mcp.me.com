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
final class GiftbagAccount extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function addMyGiftbag($data,$batch=false)
	{
        if($batch){
            return self::db()->insert($data);
        }else{
            return self::db()->insertGetId($data);
        }
	}
	
	public static function myGiftbag($uid,$pageIndex,$pageSize,$gid=0)
	{
		$tb = self::db()->where('uid','=',$uid);
		if($gid>0) $tb = $tb->where('game_id','=',$gid);
		$result = $tb->orderBy('id','desc')->forPage($pageIndex,$pageSize)->get();
		
		return $result;
	}
	
	public static function myGiftbagCount($uid,$gid=0)
	{
		$tb = self::db()->where('uid','=',$uid);
		if($gid>0) $tb = $tb->where('game_id','=',$gid);
		return $tb->count();
	}
	
	/**
	 * 获取礼包卡
	 */
	public static function getMyGiftbagInfo($giftbag_id,$uid)
	{
		$my_card = self::db()->where('gift_id','=',$giftbag_id)->where('uid','=',$uid)->first();
		
		return $my_card;
	}
}