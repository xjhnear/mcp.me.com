<?php
/**
 * 
 */

use Illuminate\Support\Facades\DB;

class Account
{
	public static function searchCount($search)
	{
		$tb = self::bindSearch($search);
		return $tb->count();
	}
	
	public static function searchList($search,$page=1,$size=10,$sort=null)
	{
		$tb = self::bindSearch($search);
		
		if($sort && is_array($sort)){
			foreach($sort as $field=>$type){
				$tb = $tb->orderBy($field,$type);
			}
		}
		$users = $tb->forPage($page,$size)->get();
		$uids = array();
		foreach($users as $user){
			$uids[] = $user['uid'];
		}
		$user_group_ids = DB::table('account_group_link')->whereIn('uid',$uids)->lists('group_id','uid');
		$groups = DB::table('account_group')->get();
		$f_group = array();
		foreach($groups as $group){
			$f_group[$group['group_id']] = $group;
		}
		unset($groups);
		$user_groups = array();
		foreach($user_group_ids as $uid=>$group_id){
			$user_groups[$uid] = $f_group[$group_id];
		}
		unset($user_group_ids);
		return array('users'=>$users,'groups'=>$user_groups);
	}
	
	protected static function bindSearch($search)
	{
		$tb = DB::table('account');
		
		if(isset($search['keyword']) && !empty($search['keyword'])){
			$tb = $tb->where('nickname','like','%'.$search['keyword'].'%');
		}
		
	    //开始时间
		if(isset($search['startdate']) && !empty($search['startdate']))
		{
			$tb = $tb->where('dateline','>=',strtotime($search['startdate'] . ' 00:00:00'));
		}
		//截至时间
		if(isset($search['enddate']) && !empty($search['enddate']))
		{
			$tb = $tb->where('dateline','<=',strtotime($search['enddate'] . ' 23:59:59'));
		}
		return $tb;
	}
	
	public static function getUserInfo($uid)
	{
		$account = DB::table('account')->where('uid','=',$uid)->first();
		$account['birthday'] = $account['birthday'] ? date('Y-m-d',$account['birthday']): '';
		
		$account['group_ids'] = DB::table('account_group_link')->where('uid','=',$uid)->lists('group_id');
		
		return $account;
	}
	
	public static function updateUserInfo($uid,$data,$group_ids)
	{
		//if(isset($data['password'])) unset($data['password']);
		!empty($data['birthday']) && $data['birthday'] = strtotime($data['birthday']);
		//return DB::table('account')->where('uid','=',$uid)->update($data);
		return Yxd\Services\UserService::updateUserInfo($uid,$data,$group_ids);
	}
	
	public static function modifyPwd($uid,$password)
	{
		return Yxd\Services\UserService::updateUserPassword($uid, $password);
	}
	
	public static function shieldField($uid,$field,$data)
	{
		return Yxd\Services\UserService::shieldAccountField($uid, $field, $data);
	}
	
	public static function getUserGroupList()
	{
		return DB::table('account_group')->orderBy('group_id','asc')->get();
	}
	
	public static function getUserGroupInfo($group_id)
	{
		return DB::table('account_group')->where('group_id','=',$group_id)->first();
	}
	
	public static function addUserGroupInfo($data)
	{
		return DB::table('account_group')->insertGetId($data);
	}
	
	public static function updateUserGroupInfo($group_id,$data)
	{
		return DB::table('account_group')->where('group_id','=',$group_id)->update($data);
	}
	
    public static function updateUserGroupAuthorize($group_id,$nodes)
	{
		return DB::table('account_group')->where('group_id','=',$group_id)->update(array('authorize_nodes'=>serialize($nodes)));
	}
	
	public static function getAuthorizeList($tree=true)
	{
		$list = DB::table('authorize_node')->orderBy('appname','asc')->orderBy('module','desc')->orderBy('id','asc')->get();
		if($tree==true){
			$tb = array();
			foreach($list as $row){
				$tb[$row['appname']]['name'] = $row['appinfo'];
				$tb[$row['appname']]['nodelist'][] = $row; 
			}
			unset($list);
			return $tb;
		}
		return $list;
	}
	
	
}