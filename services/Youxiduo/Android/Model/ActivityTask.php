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
use Illuminate\Support\Facades\Cache;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

/**
 * 活动模型类
 */
final class ActivityTask extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
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
	
	public static function buildSearch($search)
	{
		$tb = self::db();
	    if(isset($search['id'])){
			$tb = $tb->where('id','=',$search['id']);
		}
		if(isset($search['title']) && !empty($search['title'])){
			$tb = $tb->where('title','like','%'.$search['title'].'%');
		}
	    if(isset($search['in_ids']) && $search['in_ids']){
			$tb = $tb->whereIn('id',$search['in_ids']);
		}
	    if(isset($search['not_in_ids']) && $search['not_in_ids']){
			$tb = $tb->whereNotIn('id',$search['not_in_ids']);
		}
		if(isset($search['action_type'])){
			$tb = $tb->where('action_type','=',$search['action_type']);
		}
	    if(isset($search['start_time'])){
			$tb = $tb->where('start_time','<=',$search['start_time']);
		}
	    if(isset($search['end_time'])){
			$tb = $tb->where('end_time','>=',$search['end_time']);
		}
		if(isset($search['ended'])){
			$tb = $tb->where('end_time','<',$search['ended']);
		}
	    if(isset($search['is_show'])){
			$tb = $tb->where('is_show','=',$search['is_show']);
		}
	    if(isset($search['is_top'])){
			$tb = $tb->where('is_top','=',$search['is_top']);
		}
		if(isset($search['complete_type'])){
			$tb = $tb->where('complete_type','=',$search['complete_type']);
		}
		return $tb;
	}
	
	public static function findOne($search)
	{
		$cachekey = 'activity_task::'.$search['id'];
		if(Cache::has($cachekey)){
			return Cache::get($cachekey);
		}
		$tb = self::db();
		if(isset($search['id'])){
			$tb = $tb->where('id','=',$search['id']);
		}
		$info = $tb->first();
		$info && $info['complete_condition'] = $info['complete_condition'] ? json_decode($info['complete_condition'],true) : $info['complete_condition'];
		if($info){
			Cache::forever($cachekey,$info);
		}
		return $info;
	}
	
    public static function save($data)
	{
	    if(isset($data['id']) && $data['id']){
    		$id = $data['id'];
    		unset($data['id']);
    		$data['update_time'] = date('Y-m-d H:i:s');
    		$res = self::db()->where('id','=',$id)->update($data);
		    if($res){
	    		$cachekey = 'activity_task::'.$id;
	    		Cache::forget($cachekey);
	    	}
	    	return $res;
    	}else{
    		$data['create_time'] = date('Y-m-d H:i:s');
    		$data['update_time'] = date('Y-m-d H:i:s');
    		return self::db()->insertGetId($data);
    		
    	}
	}
	
	public static function delete($id)
	{
		$res = self::db()->where('id','=',$id)->delete();
		$cachekey = 'activity_task::'.$id;
	    Cache::forget($cachekey);
		return $res;
	}
}