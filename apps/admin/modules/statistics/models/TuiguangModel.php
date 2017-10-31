<?php
namespace modules\statistics\models;

use Yxd\Modules\Core\BaseModel;
use Illuminate\Support\Facades\DB;
use Yxd\Services\UserService;
class TuiguangModel extends BaseModel
{
	//获取推荐人的数目
	public static function getAllcount($data)
	{
		if(!$data) return array(); 
		$count = self::dbClubMaster()->table('account')->whereIn('uid', $data['oldids'])->count();
		if($count>500) $count=50;
		return $count;
	}
	//获取所有的推荐人信息
	public static function getTuiguangList($data, $page=0, $pagesize=10)
	{
		if(!$data)return array();
		$user_groups = self::dbClubMaster()->table('account_group')->lists('group_name');
		$pages = array_chunk($data['sortdata'],$pagesize);
		if($page>count($pages)) $page = count($pages);
		$curpage = $pages[$page-1];
		$uids = array();
		foreach($curpage as $row){
			$uids[] = $row['uid'];
		}
		$_users = self::dbClubMaster()->table('account')->whereIn('uid', $uids)->get();
		$users = array();
		foreach($_users as $row){
			$users[$row['uid']] = $row;
		}
		$out = array();
		foreach($curpage as $one){
			if(!isset($one['uid'])||!isset($users[$one['uid']])) continue;
			$d=array();
			$d['uid'] = $one['uid'];
			$d['nickname'] = $users[$one['uid']]['nickname'];
			$d['avatar'] = UserService::joinImgUrl($users[$one['uid']]['avatar']);
			$group_id = self::getGroupId($one['uid']);
			$d['group_name'] = self::getGroupName($group_id);
			$d['totol_users'] = $one['num'];
			$d['totol_credit'] = $one['score'];
			$out[] = $d;
		}
		return $out;
	}
	//获取用户的信息
	public static function getUserinfoByid($id){
		$userinfo = self::dbClubMaster()->table('account')->where('uid', $id)->first();
		if(!$userinfo) return array();
		return $userinfo;
	}
	//获取用户的金币
	public static function getUserCredit($id){
		$credit = self::dbClubMaster()->table('credit_account')->where('uid', $id)->pluck('score');
		return $credit;
	}
	
	public static function getAll($uid)
	{
		$count = self::dbClubMaster()->table('tuiguang_account')->where('oldid', $uid)->count();
		if($count > 500) $count = 500;
		return $count;
	}
	
	public static function getList($uid, $page, $pagesize)
	{
		$data = self::dbClubMaster()->table('tuiguang_account')->where('oldid', $uid)->forPage($page, $pagesize)->get();
		if(!$data) return array();
		$out = array();
		foreach($data as $key=>$v){
				$newid = $v['newid'];
				$userinfo = self::getUserinfoByid($newid);
				$out[$key]['uid'] = $userinfo['uid'];
				$out[$key]['nickname'] = $userinfo['nickname'];
				$out[$key]['email'] = $userinfo['email'];
				$out[$key]['dateline'] = $userinfo['dateline'];
				$out[$key]['score'] = self::getUserCredit($newid);
				$out[$key]['ctime'] = $v['ctime'];
		}
		return $out;
	}
	
	public static function getGroupId($uid){
		$group_id = self::dbClubMaster()->table('account_group_link')->where('uid', $uid)->pluck('group_id');
		return $group_id;
	}
	public static function getGroupName($groupid)
	{
		$group_name = self::dbClubMaster()->table('account_group')->where('group_id', $groupid)->pluck('group_name');
		return $group_name;
	}
	public static function getTuiguangIds($start, $end){
		$oldids=self::dbClubMaster()->table('tuiguang_account')->where('ctime','>=',$start)->where('ctime','<',$end)->distinct()->lists('oldid');
		if(!$oldids) return array();
		$data['oldids'] = $oldids;
		$sortdata = array();
		foreach($oldids as $oldid){
			$num = self::dbClubMaster()->table('tuiguang_account')->where('oldid', $oldid)->where('ctime','>=',$start)->where('ctime','<',$end)->count();
			$totalscore = self::dbClubMaster()->table('account_credit_history')->where('uid', $oldid)
			->whereIn('action', array('oldtuiguang_1', 'oldtuiguang_10', 'oldtuiguang_100', 'oldtuiguang_500', 'oldtuiguang_1000'))
			->where('mtime','>=',$start)->where('mtime','<',$end)
			->sum('credit');
			$data[$oldid][] = $num;
			$data[$oldid][] = $totalscore;
			$sortdata[] = array($num,'uid'=>$oldid,'num'=>$num,'score'=>$totalscore);
		}
		rsort($sortdata);
		$data['sortdata'] = $sortdata;
		return $data;
	}
}