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
final class CheckinsTaskUser extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}	
	
    public static function searchCount($search)
	{
		return self::buildSearch($search)->count();
	}
	
    public static function searchList($search,$pageIndex=1,$pageSize=10,$order=array())
	{
		$tb = self::buildSearch($search);
		foreach($order as $field=>$sort){
			$tb = $tb->orderBy($field,$sort);
		}
		return $tb->forPage($pageIndex,$pageSize)->get();
	}
	
	protected static function buildSearch($search)
	{
		$tb = self::db();
		if(isset($search['ctid']) && $search['ctid']){
			$tb = $tb->where('ctid','=',$search['ctid']);
		}
		
	    if(isset($search['uid']) && $search['uid']){
			$tb = $tb->where('uid','=',$search['uid']);
		}
		
		return $tb;
	}
	
	/**
	 * 
	 */
	public static function add($data)
	{
		$data['create_time'] = time();
		return self::db()->insertGetId($data);
	}
	
	public static function updateStatus($id,$status)
	{
		return self::db()->where('id','=',$id)->update(array('status'=>$status));
	}
}