<?php
namespace modules\statistics\models;

use Yxd\Modules\Core\BaseModel;
use Yxd\Services\UserService;

class UsercreditModel extends BaseModel
{
	//前500
	public static function getUsersCreditCount(){
		$count = self::dbClubMaster()->table('account')
									->join('credit_account','account.uid','=','credit_account.uid')
									->count();
		if($count > 500) $count = 500;
		return $count;
	}
	
	//前500
	public static function getUsersCredit($page=0,$size=10){
		$users = self::dbClubMaster()->table('account')
									->join('credit_account','account.uid','=','credit_account.uid')
									->select('account.uid','account.nickname','account.avatar','credit_account.score')
									->orderby('credit_account.score','DESC')
									->forPage($page,$size)->get();
		if(!$users) return array();
		$uids = array();
		foreach($users as $user){
			$uids[] = $user['uid'];
		}
		
		$user_group_ids = self::dbClubSlave()->table('account_group_link')->whereIn('uid',$uids)->lists('group_id','uid');
		$groups = self::dbClubSlave()->table('account_group')->lists('group_name','group_id');
		foreach ($users as &$user){
			if(array_key_exists($user['uid'], $user_group_ids)){
				$user['group_id'] = $user_group_ids[$user['uid']];
			}else{
				$user['group_id'] = 0;
			}
		}
		foreach ($users as &$user){
			if(array_key_exists($user['group_id'], $groups)){
				$user['group_name'] = $groups[$user['group_id']];
			}else{
				$user['group_name'] = '';
			}
			$user['avatar'] = UserService::joinImgUrl($user['avatar']);
		}
		return array_slice($users, 0,500);
	}
}