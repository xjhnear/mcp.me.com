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

use Illuminate\Support\Facades\Log;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
/**
 * 活动任务限制模型类
 */
final class ActivityTaskLimit extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}	
	
	public static function isLimitedDevice($atid,$idcode,$uid)
	{
		$exists = self::db()->where('atid','=',$atid)->where('idcode','=',$idcode)->first();
		return $exists ? true : false;
	}
	
	/**
	 * 添加设备限制
	 * @param int $atid 任务ID
	 * @param string $idcode 设备码
	 * @param int $uid
	 * @return bool|null 成功返回true|失败返回false|已存在返回null
	 *
	 */
	public static function addLimitedDevice($atid,$idcode,$uid)
	{
		$exists = self::isLimitedDevice($atid,$idcode,$uid);
		if($exists==true) return false;
		$data = array('atid'=>$atid,'idcode'=>$idcode,'uid'=>$uid,'create_at'=>date('Y-m-d H:i:s'));
		try{
			$id = self::db()->insertGetId($data);
			return $id ? true : null;
		}catch(\Exception $e){
			Log::error($e);
			return false;
		}
		return false;
	}
}