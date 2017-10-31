<?php
namespace modules\activity\models;
use Yxd\Modules\Core\BaseModel;

class ActivityModel extends BaseModel
{
	public static $TypeList = array('1'=>'问答活动','2'=>'论坛活动');
	
	/**
	 * 搜索活动
	 */
	public static function search($search,$page=1,$size=10)
	{
		$total = self::buildSearch($search)->count();
		$list = self::buildSearch($search)->forPage($page,$size)->orderBy('sort','desc')->orderBy('id','desc')->get();
		
		return array('result'=>$list,'total'=>$total);
	}
	
    protected static function buildSearch($search)
	{
		$tb = self::dbClubSlave()->table('activity');
		
		if(isset($search['id']) && !empty($search['id'])){
			$tb = $tb->where('id','=',$search['id']);
		}
		
	    if(isset($search['type']) && !empty($search['type'])){
			$tb = $tb->where('type','=',$search['type']);
		}
	    if(isset($search['title']) && !empty($search['title'])){
			$tb = $tb->where('title','like','%'.$search['title'].'%');
		}
		
	    //开始时间
		if(isset($search['startdate']) && !empty($search['startdate']))
		{
			$tb = $tb->where('startdate','>=',strtotime($search['startdate'] . ' 00:00:00'));
		}
		//截至时间
		if(isset($search['enddate']) && !empty($search['enddate']))
		{
			$tb = $tb->where('enddate','<=',strtotime($search['enddate'] . ' 23:59:59'));
		}
		
	    
		
		return $tb;
	}
	
	/**
	 * 活动基本信息
	 */
	public static function getInfo($id)
	{
		$activity = self::dbClubSlave()->table('activity')->where('id','=',$id)->first();
		
		return $activity;
	}
	/**
	 * 保存活动信息
	 */
	public static function save($data)
	{
	    if(isset($data['id']) && $data['id']){
			$id = $data['id'];
			unset($data['id']);
			self::dbClubMaster()->table('activity')->where('id','=',$id)->update($data);
			return true;
		}else{
			$data['status'] = 0;
			$data['addtime'] = time();
			return self::dbClubMaster()->table('activity')->insertGetId($data);
		}
	}
	
	/**
	 * 更新状态
	 */
	public static function updateStatus($id,$status)
	{
		return self::dbClubMaster()->table('activity')->where('id','=',$id)->update(array('status'=>$status));
	}
	
	/**
	 * 更新活动规则
	 */
    public static function updateRule($act_id,$tid)
	{
		return self::dbClubMaster()->table('activity')->where('id','=',$act_id)->update(array('rule_id'=>$tid));		
	}
}