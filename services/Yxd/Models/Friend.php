<?php
namespace Yxd\Models;

use Illuminate\Support\Facades\DB as DB;

class Friend
{
    public static function addFriend($uid,$fuid,$group_id=0)
	{
		$data['uid'] = $uid;
		$data['fuid'] = $fuid;
		$data['friend_group_id'] = $group_id;
		$data['ctime'] = (int)microtime(true);
		$count = DB::table('account_friend')->where('uid','=',$uid)->where('fuid','=',$fuid)->count();
		if($count!==0){
			return -1;
		}else{
			return DB::table('account_friend')->insertGetId($data);
		}
	}
	
	public static function isFriend($uid,$fuid)
	{
		$count = DB::table('account_friend')->where('uid','=',$uid)->where('fuid','=',$fuid)->count();
		return $count>0 ? true : false;
	}
	
	public static function deleteFriend($uid,$fuid)
	{
	    $count = DB::table('account_friend')->where('uid','=',$uid)->where('fuid','=',$fuid)->count();
		if($count===0){
			return -1;
		}
		return DB::table('account_friend')->where('uid','=',$uid)->where('fuid','=',$fuid)->delete();
	}
	public static function getFriendCount($uid)
	{
		return DB::table('account_friend')->where('uid','=',$uid)->count();
	}
	public static function getFriendList($uid,$page=1,$pagesize=20)
	{
		$uids = DB::table('account_friend')->where('uid','=',$uid)->forPage($page,$pagesize)->lists('fuid');
		return $uids;
	}
}