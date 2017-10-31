<?php
namespace modules\activity\models;
use Yxd\Modules\Core\BaseModel;

class GameAskModel extends BaseModel
{
	/**
	 * 更新问答规则
	 */
	public static function updateRule($act_id,$tid)
	{
		return self::dbClubMaster()->table('activity')->where('id','=',$act_id)->update(array('rule_id'=>$tid));		
	}	
	
    public static function searchGameAskCount($search)
	{
		return self::buildSearch($search)->count();
	}
	
	public static function searchGameAskList($search,$page=1,$pagesize=15,$orderField='addtime',$orderType='desc')
	{
		$tb = self::buildSearch($search);
		return $tb->forPage($page,$pagesize)->orderBy($orderField,$orderType)->get();		
	}
	
	public static function buildSearch($search)
	{
		$tb = self::dbClubSlave()->table('activity_ask');
		
		if(isset($search['id']) && !empty($search['id'])){
			$tb = $tb->where('id','=',$search['id']);
		}
		
	    if(isset($search['title']) && !empty($search['title'])){
			$tb = $tb->where('title','like','%'.$search['title'].'%');
		}
		
		return $tb;
	}
	
	/**
	 * 获取问答信息
	 */
	public static function getInfo($id)
	{
		return self::dbClubSlave()->table('activity_ask')->where('id','=',$id)->first();
	}
	
	/**
	 * 保存问答信息
	 */
	public static function save($data)
	{
		$id = $data['id'];
		$count = self::dbClubMaster()->table('activity_ask')->where('id','=',$id)->count();
	    if($count){			
			return self::dbClubMaster()->table('activity_ask')->where('id','=',$id)->update($data);
		}else{
			return self::dbClubMaster()->table('activity_ask')->insert($data);
		}
	}
	
	/**
	 * 问答活动发布进度
	 */
	public static function getProcess($act_ids)
	{
		$prizes = self::dbClubMaster()->table('activity_ask')->whereIn('id',$act_ids)->lists('id');
		
		$ask_ids = self::dbClubMaster()->table('activity_ask_question')->whereIn('ask_id',$act_ids)->lists('ask_id');
		
		$data = array('prize_ids'=>array_unique($prizes),'ask_ids'=>array_unique($ask_ids));
		//print_r($data);exit;
		return $data;
	}
	
	public static function getAllAskResult($ask_id)
	{
		return self::dbClubMaster()->table('activity_ask_account')->where('ask_id','=',$ask_id)->get();
	}	
}