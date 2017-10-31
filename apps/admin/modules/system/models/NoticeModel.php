<?php
namespace modules\system\models;
use Yxd\Modules\Core\BaseModel;

class NoticeModel extends BaseModel
{
    /**
	 * 获取通知设置列表
	 */
	public static function getList()
	{
		return self::dbClubSlave()->table('notice_setting')->get();
	}
	
	/**
	 * 获取通知设置项信息
	 */
	public static function getInfo($id)
	{
		return self::dbClubSlave()->table('notice_setting')->where('id','=',$id)->first();
	}
	
	/**
	 * 保存通知设置项信息
	 */
	public static function save($data)
	{
		if(isset($data['id']) && $data['id']>0){
			$id = $data['id'];
			unset($data['id']);
			return self::dbClubMaster()->table('notice_setting')->where('id','=',$id)->update($data);
		}else{
			return self::dbClubMaster()->table('notice_setting')->insertGetId($data);
		}
	}
}