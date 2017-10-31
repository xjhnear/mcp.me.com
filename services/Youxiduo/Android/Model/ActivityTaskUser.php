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
final class ActivityTaskUser extends Model implements IModel
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
	
	public static function findOne($search)
	{
		$info = self::buildSearch($search)->first();
		return $info;
	}
	
	public static function buildSearch($search)
	{
		$tb = self::db();
	    if(isset($search['id']) && $search['id']){
			$tb = $tb->where('id','=',$search['id']);
		}
		if(isset($search['atid']) && $search['atid']){
			$tb = $tb->where('atid','=',$search['atid']);
		}
	    if(isset($search['uid'])){
			$tb = $tb->where('uid','=',$search['uid']);
		}
	    if(isset($search['complete_status'])){
			$tb = $tb->where('complete_status','=',$search['complete_status']);
		}
	    if(isset($search['reward_status'])){
			$tb = $tb->where('reward_status','=',$search['reward_status']);
		}
		
		if(isset($search['not_in_atid']) && $search['not_in_atid']){
			$tb = $tb->whereNotIn('atid',$search['not_in_atid']);
		}
		
	    if(isset($search['in_atid']) && $search['in_atid']){
			$tb = $tb->whereIn('atid',$search['in_atid']);
		}

	    if(isset($search['start_time'])){
			$tb = $tb->where('create_time','>=',strtotime($search['start_time'].' 00:00:00'));
		}
	    if(isset($search['end_time'])){
			$tb = $tb->where('create_time','<=',strtotime($search['end_time'] . ' 23:59:59'));
		}
		
		return $tb;
	}
	
	public static function searchTaskIds($search)
	{
		return self::buildSearch($search)->lists('atid');
	}
	
	public static function searchTaskStatus($search)
	{
		return self::buildSearch($search)->lists('complete_status','atid');
	}
	
	public static function saveAddOrUpdate($data)
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
	
	public static function updateCompleteStatus($id,$complete_status,$cct=false)
	{
		$data = array('complete_status'=>$complete_status);
		if($cct==true){
			return self::db()->where('id','=',$id)->where('complete_status','=',2)->update($data);
		}
		return self::db()->where('id','=',$id)->update($data);
	}
	
    public static function updateRewardStatus($id,$reward_status)
	{
		$data = array('reward_status'=>$reward_status);
		return self::db()->where('id','=',$id)->update($data);
	}
	
	public static function updateStatus($id,$data,$cct=false)
	{
		if(!$id) return false;
	    if($cct==true){
			return self::db()->where('id','=',$id)->where('reward_status','=',0)->update($data);
		}
		return self::db()->where('id','=',$id)->update($data);
	}
	
	public static function updateStatusNum()
	{
		$sql_init = 'update yxd_activity_task set status_success_num=0,status_fail_num=0,status_all_num=0,status_wait_num=0';
		self::execUpdateBySql($sql_init);
		//已完成
		$sql_success = 'update yxd_activity_task a,(select c.atid,count(*) as total from yxd_activity_task_user c where c.complete_status=1 group by c.atid) b set a.status_success_num=b.total where a.id=b.atid';
		self::execUpdateBySql($sql_success);
		//失败
		$sql_fail = 'update yxd_activity_task a,(select c.atid,count(*) as total from yxd_activity_task_user c where c.complete_status=3 group by c.atid) b set a.status_fail_num=b.total where a.id=b.atid';
		self::execUpdateBySql($sql_fail);
		//待审核
		$sql_wait = 'update yxd_activity_task a,(select c.atid,count(*) as total from yxd_activity_task_user c where c.complete_status=2 group by c.atid) b set a.status_wait_num=b.total where a.id=b.atid';
		self::execUpdateBySql($sql_wait);
		//总数
		$sql_all = 'update yxd_activity_task a,(select c.atid,count(*) as total from yxd_activity_task_user c group by c.atid) b set a.status_all_num=b.total where a.id=b.atid';
		self::execUpdateBySql($sql_all);
	}
}