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
 * 活动截图模型类
 */
final class ActivityTaskUserScreenshot extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}	
	
	public static function add($data)
	{
		return self::db()->insert($data);
	}
	
    public static function searchCount($search)
	{
		return self::buildSearch($search)->count();
	}
	
	public static function searchList($search,$pageIndex=1,$pageSize=10,$sort=array())
	{
		$tb = self::buildSearch($search);
		foreach($sort as $field=>$order){
			$tb = $tb->orderBy($field,$order);
		}
		return $tb->forPage($pageIndex,$pageSize)->get();
	}
	
    protected static function buildSearch($search)
	{
		$tb = self::db();
	    if(isset($search['id']) && $search['id']){
			$tb = $tb->where('id','=',$search['id']);
		}
		if(isset($search['atid']) && $search['atid']){
			$tb = $tb->where('atid','=',$search['atid']);
		}
	    if(isset($search['uid']) && $search['uid']){
			$tb = $tb->where('uid','=',$search['uid']);
		}	    
		
		return $tb;
	}
	
}