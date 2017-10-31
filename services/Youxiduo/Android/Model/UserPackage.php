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
 * 用户匹配游戏包模型类
 */
final class UserPackage extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function saveMatchData($packages)
	{
		if(!$packages) return false;
		$exists_pkg = self::db()->whereIn('packagename',$packages)->lists('packagename');
		
		$not_exists_pkg = array_diff($packages,$exists_pkg);
		$data = array();
		foreach($not_exists_pkg as $pkg){
			$data[] = array('packagename'=>$pkg,'ctime'=>time());
		}
		if($data) self::db()->insert($data);
	}	
	
	public static function getAllPackage()
	{
		$result = self::db()->lists('packagename');
		
		return $result;
	}
}