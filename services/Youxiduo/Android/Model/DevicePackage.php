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
 * 游戏包模型类
 */
final class DevicePackage extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function isExists($uid,$idcode,$package)
	{
		$info = self::db()->where('idcode','=',$idcode)->where('package','=',$package)->first();
		if(!$info){
			self::db()->insert(array('idcode'=>$idcode,'package'=>$package,'uid'=>0,'ctime'=>time()));
			return false;
		}
		return true;
	}
}