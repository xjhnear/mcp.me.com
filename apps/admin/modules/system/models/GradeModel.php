<?php
namespace modules\system\models;
use Yxd\Modules\Core\BaseModel;

class GradeModel extends BaseModel
{
	/**
	 * 获取等级设置列表
	 */
	public static function getList()
	{
		return self::dbClubSlave()->table('credit_level')->orderBy('start','asc')->get();
	}
	
	/**
	 * 获取等级设置项信息
	 */
	public static function getInfo($id)
	{
		return self::dbClubSlave()->table('credit_level')->where('id','=',$id)->first();
	}
	
	/**
	 * 保存等级设置项信息
	 */
	public static function save($data)
	{
		if(isset($data['id']) && $data['id']>0){
			$id = $data['id'];
			unset($data['id']);
			return self::dbClubMaster()->table('credit_level')->where('id','=',$id)->update($data);
		}else{
			return self::dbClubMaster()->table('credit_level')->insertGetId($data);
		}
	}
}