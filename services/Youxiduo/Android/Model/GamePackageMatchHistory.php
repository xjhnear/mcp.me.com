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
final class GamePackageMatchHistory extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function saveMatchHistory($uid,$pkg_list)
	{
		if(!$pkg_list) return false;
		$data = array();
		foreach($pkg_list as $pkg){
			$data[] = array(
			    'packagename'=>$pkg,
			    'uid'=>$uid,
			    'ctime'=>time()
			);
		} 
		if($data){
			self::db()->insert($data);
		}
		return false;
	}
	
	public static function getMatchCountByPackageName(array $pkg_list)
	{
		if(!$pkg_list) return array();
		return self::db()->whereIn('packagename',$pkg_list)->groupBy('packagename')->select(self::raw('packagename,count(*) as total'))->lists('total','packagename');
	}
}