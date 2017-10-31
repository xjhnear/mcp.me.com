<?php
namespace modules\v4user\models;
use modules\comment\models\CommentModel;

use modules\forum\models\TopicModel;

use Yxd\Modules\Core\BaseModel;
use Yxd\Services\UserService;

class UserModel extends BaseModel
{
    public static function searchCount($search)
	{
		$tb = self::bindSearch($search);
		return $ct = $tb->count();
	}
	
	public static function searchList($search,$page=1,$size=10,$sort=null)
	{
		$tb = self::bindSearch($search);
		
		if($sort && is_array($sort)){
			foreach($sort as $field=>$type){
				if($field=='dateline'){
					$field = 'uid';
				}
				$tb = $tb->orderBy($field,$type);
			}
		}
		
		$users = $tb->forPage($page,$size)->get();
		if(!$users) return array('users'=>array(),'groups'=>array());
		$uids = array();
		foreach($users as $user){
			$uids[] = $user['uid'];
		}
		
		//$credits = self::dbClubSlave()->table('credit_account')->whereIn('uid',$uids)->lists('score','uid');
		
		$user_group_ids = self::dbClubSlave()->table('account_group_link')->whereIn('uid',$uids)->lists('group_id','uid');
		$groups = self::dbClubSlave()->table('account_group')->get();
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
		foreach($users as $key=>$row){
			//$row['credit'] = isset($credits[$row['uid']]) ? $credits[$row['uid']] : 0;
			$row['avatar'] = UserService::joinImgUrl($row['avatar']); 
			$users[$key] = $row;
		}
		return array('users'=>$users,'groups'=>$user_groups);
	}
	
	protected static function bindSearch($search)
	{
		//$tb = self::dbClubSlave()->table('account')->leftJoin('credit_account','account.uid','=','credit_account.uid')->select('account.*','credit_account.score','credit_account.experience');
		$tb = self::dbClubSlave()->table('account');
		if(isset($search['keyword']) && !empty($search['keyword'])){
			if(isset($search['keytype']) && !empty($search['keytype'])){
				switch($search['keytype']){
					case 'nickname':
						$tb = $tb->where('account.nickname','like','%'.$search['keyword'].'%');
						break;
					case 'uid':
						$tb = $tb->where('account.uid','=',(int)$search['keyword']);
						break;
					case 'email':
						$tb = $tb->where('account.email','=',$search['keyword']);
						break;
					case 'mobile':
						$tb = $tb->where('account.mobile','=',$search['keyword']);
						break;
				}				
			}
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
		//$account = self::dbClubSlave()->table('account')->where('uid','=',$uid)->first();
		$account = UserService::getUserInfo($uid,'full');
		$account['birthday'] = $account['birthday'] ? date('Y-m-d',$account['birthday']): '';
		
		$account['group_ids'] = self::dbClubSlave()->table('account_group_link')->where('uid','=',$uid)->lists('group_id');
		
		return $account;
	}
	
	public static function getUsersInfo($uids=array()){
		if(!$uids || !is_array($uids)) return array();
		return self::dbClubMaster()->table('account')->whereIn('uid',$uids)
									->select('uid','username','nickname','avatar','dateline')->get();
	}
	
	public static function getUsersGroups($uids = array()){
		if(!$uids || !is_array($uids)) return array();
		return self::dbClubMaster()->table('account_group_link')->whereIn('uid',$uids)->lists('group_id','uid');
	}
	
	public static function createUserInfo()
	{
		
	}
	
	public static function updateUserInfo($uid,$data,$group_ids)
	{
		//if(isset($data['password'])) unset($data['password']);
		!empty($data['birthday']) && $data['birthday'] = strtotime($data['birthday']);
		//return DB::table('account')->where('uid','=',$uid)->update($data);
		return UserService::updateUserInfo($uid,$data,$group_ids);
	}

	public static function updateArea($uid,$area)
	{
		\Youxiduo\V4\User\UserService::updateUserArea($uid,$area);
	}
	
	public static function modifyPwd($uid,$password)
	{
		return UserService::updateUserPassword($uid, $password);
	}
	
	public static function modifyEmail($uid,$email)
	{
		return UserService::updateUserEmail($uid, $email);
	}
	
	public static function shieldField($uid,$field,$data)
	{
		return UserService::shieldAccountField($uid, $field, $data);
	}
	
	public static function getUserGroupList()
	{
		return self::dbClubSlave()->table('account_group')->orderBy('group_id','asc')->get();
	}
	
	public static function getGroupNameList()
	{
		return self::dbClubSlave()->table('account_group')->orderBy('group_id','asc')->lists('group_name','group_id');
	}
	
	public static function getUserGroupInfo($group_id)
	{
		return self::dbClubSlave()->table('account_group')->where('group_id','=',$group_id)->first();
	}
	
	public static function addUserGroupInfo($data)
	{
		return self::dbClubSlave()->table('account_group')->insertGetId($data);
	}
	
	public static function updateUserGroupInfo($group_id,$data)
	{
		return self::dbClubSlave()->table('account_group')->where('group_id','=',$group_id)->update($data);
	}
	
    public static function updateUserGroupAuthorize($group_id,$nodes)
	{
		return self::dbClubSlave()->table('account_group')->where('group_id','=',$group_id)->update(array('authorize_nodes'=>serialize($nodes)));
	}
	
	public static function getAuthorizeList($tree=true)
	{
		$list = self::dbClubSlave()->table('authorize_node')->orderBy('appname','asc')->orderBy('module','desc')->orderBy('id','asc')->get();
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
	
	public static function getBanList($uids=array())
	{
		return self::dbClubSlave()->table('account_ban')->where('type','=',1)->where(function($query){
		    $query = $query->where('expired','=',0)->orWhere('expired','>=',time());
		})->lists('uid');
	}
	
	public static function doUnban($uid,$type)
	{
		return self::dbClubSlave()->table('account_ban')->where('uid','=',$uid)->where('type','=',$type)->delete();
	}
	
	public static function doBan($uid,$type,$expired)
	{
		$count = self::dbClubSlave()->table('account_ban')->where('uid','=',$uid)->where('type','=',$type)->count();
		if($count){
			return self::dbClubSlave()->table('account_ban')->where('uid','=',$uid)->where('type','=',$type)->update(array('expired'=>$expired));
		}else{
			$data = array(
			    'uid'=>$uid,
			    'type'=>$type,
			    'ctime'=>time(),
			    'expired'=>$expired,
			);
			return self::dbClubSlave()->table('account_ban')->insertGetId($data);
		}
	}
	
	/**
	 * 获取游币记录
	 */
	public static function getCreditHistory($search,$page=1,$size=10)
	{
		$total = self::buildCreditHistory($search)->count();
		$result = self::buildCreditHistory($search)->forPage($page,$size)->orderBy('mtime','desc')->get();
		return array('result'=>$result,'total'=>$total);
	}
	
	protected static function buildCreditHistory($search)
	{
		$tb = self::dbClubSlave()->table('account_credit_history');
		if(isset($search['uid']) && $search['uid']){
			$tb = $tb->where('uid','=',$search['uid']);
		}
		
		return $tb;
	}
	
    public static function clearUserTopicAndComment($uid)
	{
		$tids = self::dbClubSlave()->table('forum_topic')->where('author_uid','=',$uid)->select('tid')->lists('tid');
		if($tids){
			TopicModel::delTopic($tids);
		}
		$cids = self::dbClubSlave()->table('comment')->where('uid','=',$uid)->select('id')->lists('id');
		if($cids){
			CommentModel::doDelete($cids);
		}
	}
	
	public static function SearchUids($search)
	{
	    $tb = self::dbClubSlave()->table('account');
	    //开始时间
	    if(isset($search['startdate']) && !empty($search['startdate']))
	    {
	        $tb = $tb->where('dateline','>=',strtotime($search['startdate']));
	    }
	    //截至时间
	    if(isset($search['enddate']) && !empty($search['enddate']))
	    {
	        $tb = $tb->where('dateline','<=',strtotime($search['enddate']));
	    }
	    //平台
	    if(isset($search['appType']) && !empty($search['appType']))
	    {
	        if ($search['appType'] == 2) {
	            $tb = $tb->where('client','glwzry');
	        } else {
	            $tb = $tb->whereIn('client', array('ios', ''));
	        }
	    }
	    $tb = $tb->lists('uid');
	    return $tb;
	}
	
}