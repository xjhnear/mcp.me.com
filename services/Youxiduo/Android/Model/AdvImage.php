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
 * 图片广告模型类
 */
final class AdvImage extends Model implements IModel
{	
	public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function findOne($search)
	{
		return self::buildSearch($search)->first();
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
	    if(isset($search['id'])){
			$tb = $tb->where('id','=',$search['id']);
		}
		
		if(isset($search['in_id'])){
			$tb = $tb->whereIn('id',$search['in_id']);
		}
		
		if(isset($search['place_id'])){
			$tb = $tb->where('place_id','=',$search['place_id']);
		}
		if(isset($search['place_type'])){
			$tb = $tb->where('place_type','=',$search['place_type']);
		}
		if(isset($search['effective'])){
			$tb = $tb->where('start_time','<=',time())->where('end_time','>=',time());
		}
		if(isset($search['is_show'])){
			$tb = $tb->where('is_show','=',$search['is_show']);
		}
		return $tb;
	}
	
	public static function saveAddOrUpdate($data)
	{
	    if(isset($data['id']) && $data['id']){
    		$id = $data['id'];
    		unset($data['id']);
    		$data['update_time'] = date('Y-m-d H:i:s');
    		return self::db()->where('id','=',$id)->update($data);
    	}else{
    		$data['create_time'] = date('Y-m-d H:i:s');
    		$data['update_time'] = date('Y-m-d H:i:s');
    		return self::db()->insertGetId($data);
    	}
	}
}