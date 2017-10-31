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
final class GamePackageCollect extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
    /**
	 * 通过包名获取游戏列表
	 */
	public static function getGameListByPackage(array $pkg_list)
	{
		if(!$pkg_list) return array();
		$result = self::db()->whereIn('apk_packagename',$pkg_list)->get();
		
		return $result;
	}
	
    public static function updateMatchCount($packages,$idcode)
	{
		foreach($packages as $key=>$package){
		    if($idcode && DevicePackage::isExists(0, $idcode, $package)==true){
		    	unset($packages[$key]);
		    }
		}
		!empty($packages) && self::db()->whereIn('apk_packagename',$packages)->increment('MATCH_COUNT');
		return true;
	}
	
    public static function m_searchGameListByPackage(array $pkg_list,$pageIndex=1,$pageSize=10)
	{
		if(!$pkg_list) return array('result'=>array(),'total'=>0);
		$search['pkg_list'] = $pkg_list;
		$total = self::m_buildSearch($search)->count();
		$result = self::m_buildSearch($search)->forPage($pageIndex,$pageSize)->get();		
		return array('result'=>$result,'total'=>$total);
	}
	
	protected static function m_buildSearch($search)
	{		
		$tb = self::db();
		if(isset($search['pkg_list']) && is_array($search['pkg_list']) && $search['pkg_list']){
			$tb = $tb->whereIn('apk_packagename',$search['pkg_list']);
		}
		return $tb;
	}
}