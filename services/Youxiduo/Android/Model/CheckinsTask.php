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
final class CheckinsTask extends Model implements IModel
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
		if(isset($search['type'])){
			$tb = $tb->where('type','=',$search['type']);
		}
		
	    if(isset($search['id'])){
			$tb = $tb->where('id','=',$search['id']);
		}
		if(isset($search['start_time']) && $search['start_time']){
			$tb = $tb->where('start_time','<=',$search['start_time']);
		}
		
	    if(isset($search['end_time']) && $search['end_time']){
			$tb = $tb->where('end_time','>=',$search['end_time']);
		}
		if(isset($search['is_show'])){
			$tb = $tb->where('is_show','=',$search['is_show']);
		}
		return $tb;
	}
	
	public static function findOne($search)
	{
		$info = self::buildSearch($search)->first();
		return $info;
	}
	
	public static function save($data)
	{
	    if(isset($data['id']) && $data['id']){
    		$id = $data['id'];
    		unset($data['id']);
    		$data['update_time'] = time();
    		return self::db()->where('id','=',$id)->update($data);
    	}else{
    		$data['create_time'] = time();
    		$data['update_time'] = time();
    		return self::db()->insertGetId($data);
    	}
	}
}