<?php
namespace modules\activity\models;
use Yxd\Modules\Core\BaseModel;
use Yxd\Services\UserService;

class HuntModel extends BaseModel
{
	/**
	 * 更新寻宝箱规则
	 */
	public static function updateRule($act_id,$tid)
	{
		return self::dbClubMaster()->table('activity_hunt')->where('id','=',$act_id)->update(array('rule_id'=>$tid));		
	}	    
	
    public static function searchCount($search)
	{
		$tb = self::buildSearch($search);
		return $tb->count();
	}
	
	public static function searchList($search,$page=1,$pagesize=10)
	{
		$tb = self::buildSearch($search);
		return $tb->forPage($page,$pagesize)->orderBy('startdate','desc')->get();
	}
	
	protected static function buildSearch($search)
	{
		$tb = self::dbClubMaster()->table('activity_hunt');
		
		return $tb;
	}
	
    public static function getInfo($id)
	{
		$tb = self::dbClubMaster()->table('activity_hunt');
		$hunt = $tb->where('id','=',$id)->first();
		if($hunt){
			$hunt['first_prize'] = json_decode($hunt['first_prize'],true);			
			$hunt['second_prize'] = json_decode($hunt['second_prize'],true);
			$hunt['third_prize'] = json_decode($hunt['third_prize'],true);
		}
		return $hunt;
	}
	
	/**
	 * 保存寻宝箱信息
	 */
	public static function save($data)
	{
		$tb = self::dbClubMaster()->table('activity_hunt');
		if(isset($data['id']) && $data['id']){
			$id = $data['id'];
			unset($data['id']);
			$tb->where('id','=',$id)->update($data);
			return true;
		}else{
			$data['addtime'] = time();
			return $tb->insertGetId($data);
		}
	}
}