<?php
namespace modules\activity\models;
use Yxd\Services\UserService;

use Yxd\Modules\Core\BaseModel;

class AskQuestionModel extends BaseModel
{
    public static function getList($ask_id)
	{
		$list = self::dbClubMaster()->table('activity_ask_question')->where('ask_id','=',$ask_id)->orderBy('sort','asc')->get();
		foreach($list as $key=>$row){
			$row['options'] = json_decode($row['options'],true);
			$list[$key] = $row;
		}
		return $list;
	}
	
	public static function getInfo($id)
	{
		$info = self::dbClubMaster()->table('activity_ask_question')->where('id','=',$id)->first();
		$info['options'] = json_decode($info['options'],true);
		return $info;
	}
	
	public static function save($data)
	{
	    if(isset($data['id']) && $data['id']){
			$id = $data['id'];
			unset($data['id']);
			self::dbClubMaster()->table('activity_ask_question')->where('id','=',$id)->update($data);
			return true;
		}else{
			$data['addtime'] = time();
			return self::dbClubMaster()->table('activity_ask_question')->insertGetId($data);
		}
	}
	
	/**
	 * 满分用户
	 */
	public static function searchFullMark($ask_id,$page=1,$size=10)
	{
		$total = self::dbClubSlave()->table('activity_ask_account')
		->where('ask_id','=',$ask_id)
		->where('result','=',100)
		->count();
		$list = self::dbClubSlave()->table('activity_ask_account')
		->where('ask_id','=',$ask_id)
		->where('result','=',100)
		->forPage($page,$size)
		->orderBy('addtime','asc')
		->get();
		$uids = array();
		foreach($list as $row){
			$uids[] = $row['uid'];
		}
		$users = UserService::getBatchUserInfo($uids);
		foreach($list as $key=>$row){
			$row['user'] = $users[$row['uid']];
			$list[$key] = $row;
		}
		return array('result'=>$list,'total'=>$total);
	}
}