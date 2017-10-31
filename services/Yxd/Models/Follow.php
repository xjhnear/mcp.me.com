<?php
namespace Yxd\Models;

use Illuminate\Support\Facades\DB as DB;

class Follow
{
    public static function addFollow($uid,$fuid)
	{
		$data['uid'] = $uid;
		$data['fuid'] = $fuid;
		$data['ctime'] = (int)microtime(true);
		$count = DB::table('account_follow')->where('uid','=',$uid)->where('fuid','=',$fuid)->count();
		if($count>0){
			return -1;
		}else{
			return DB::table('account_follow')->insertGetId($data);
		}
	}
	
	public static function isFollow($uid,$fuid)
	{
		$count = DB::table('account_follow')->where('uid','=',$uid)->where('fuid','=',$fuid)->count();
		return $count>0 ? true : false;
	}
	
	public static function deleteFollow($uid,$fuid)
	{
	    $count = DB::table('account_follow')->where('uid','=',$uid)->where('fuid','=',$fuid)->count();
		if($count===0){
			return -1;
		}
		return DB::table('account_follow')->where('uid','=',$uid)->where('fuid','=',$fuid)->delete();
	}
	
	public static function getFollowCount($uid)
	{
		return DB::table('account_follow')->where('uid','=',$uid)->count();
	}
	
	public static function getFollowList($uid,$page=1,$pagesize=20)
	{
		$uids = DB::table('account_follow')->where('uid','=',$uid)->forPage($page,$pagesize)->lists('fuid');

		return $uids;
	}
	public static function getFollowerCount($uid)
	{
		return DB::table('account_follow')->where('fuid','=',$uid)->count();
	}
	
    public static function getFollowerList($uid,$page=1,$pagesize=20)
	{
		$uids = DB::table('account_follow')->where('fuid','=',$uid)->forPage($page,$pagesize)->lists('uid');

		return $uids;
	}
}