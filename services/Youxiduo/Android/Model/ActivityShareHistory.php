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
 * 活动模型类
 */
final class ActivityShareHistory extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function saveForNotExists($atid,$uid,$ip)
	{
		$exists = self::db()->where('atid','=',$atid)->where('uid','=',$uid)->where('ip','=',$ip)->first();
		if($exists) return true;
		$data = array('atid'=>$atid,'uid'=>$uid,'ip'=>$ip,'create_at'=>date('Y-m-d H:i:s'));
		return self::db()->insert($data);
	}
	
	public static function getIpCount($atid,$uid)
	{
		return self::db()->where('atid','=',$atid)->where('uid','=',$uid)->count();
	}
}