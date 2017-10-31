<?php
namespace modules\activity\models;
use Yxd\Modules\Core\BaseModel;

class PrizeModel extends BaseModel
{
	/**
	 * 搜索奖品
	 */	
    public static function searchCount($search)
	{
		return self::buildSearch($search)->count();
	}
	
	/**
	 * 
	 */
	public static function searchList($search,$page=1,$pagesize=15,$orderField='addtime',$orderType='desc')
	{
		$tb = self::buildSearch($search);
		return $tb->forPage($page,$pagesize)->orderBy($orderField,$orderType)->get();		
	}
	
	/**
	 * 
	 */
	public static function buildSearch($search)
	{
		$tb = self::dbClubMaster()->table('activity_prize');
		
		return $tb;
	}
	
	public static function getListByIds($ids)
	{
		$list = self::dbClubMaster()->table('activity_prize')->whereIn('id',$ids)->get();
		$prizes = array();
		foreach($list as $row){
			$prizes[$row['id']] = $row;
		}
		return $prizes;
	}
	
	/**
	 * 
	 */
	public static function getInfo($id)
	{
		return self::dbClubMaster()->table('activity_prize')->where('id','=',$id)->first();
	}
	
	/**
	 * 保存信息
	 */
	public static function save($data)
	{
		if(isset($data['id']) && $data['id']){
			$id = $data['id'];
			unset($data['id']);
			self::dbClubMaster()->table('activity_prize')->where('id','=',$id)->update($data);
			return true;
		}else{
			$data['addtime'] = time();
			return self::dbClubMaster()->table('activity_prize')->insertGetId($data);
		}
	}
	
}