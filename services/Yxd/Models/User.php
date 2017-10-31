<?php
namespace Yxd\Models;

use Yxd\Modules\Core\CacheService;
use Yxd\Modules\Core\BaseModel;
use Illuminate\Support\Facades\DB;
use Yxd\Services\TaskService;
class User extends BaseModel
{
	/*
	 * 检查用户是否存在
	 */
	public static function checkUserById($uid)
	{
		return DB::table('account')->where('uid', $uid)->count();
	}
	//获取用户游币的值
	public static function getScore($uid)
	{
		$res = DB::table('credit_account')->where('uid', $uid)->first();
		return $res['score'];
	}
	
    public static function getZhucema()
	{
	    $chars_array = array(
	        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
	        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k',
	        'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v',
	        'w', 'x', 'y',
	    );
	    $charsLen = count($chars_array) - 1;
	    $outputstr = "";
	    for ($i=0; $i<10; $i++)
	    {
	    $outputstr .= $chars_array[mt_rand(0, $charsLen)];
	    }
	    $out = array();
	    if(in_array($outputstr, $out)){
	    	self::getZhucema();
	    }else{
	    	$out[] = $outputstr;
	    }
	    return $outputstr;
	}

	/**
	 * 创建用户
	 */
	public static function createAccount($user)
	{
		$account = array();
		$fields = array('nickname','email','password','avatar','sex','mobile','birthday','summary','homebg','reg_ip','vuser');		
		foreach($user as $field=>$data){
			if(in_array($field,$fields)){
				if($field=='password'){
					$account[$field] = self::cryptPwd($data);
				}else{
				    $account[$field] = $data;
				}
			}
		}
		$account['dateline'] = (int)microtime(true);
		$account['zhucema'] = self::getZhucema();
		$uid = DB::table('account')->insertGetId($account);
		if($uid){
			$user['uid'] = $uid;
			DB::table('account_group_link')->insert(array('uid'=>$uid,'group_id'=>5));
			if(!isset($account['nickname']) || empty($account['nickname'])){
				$nickname = '玩家' . $uid;
				DB::table('account')->where('uid','=',$uid)->update(array('nickname'=>$nickname));
				$user['nickname'] = $nickname;
			}
			return $user;
		}
		return null;
	} 
	
	public static function getUserInfoList($uids)
	{		
		if(is_array($uids)){
			return DB::table('account')->whereIn('uid',$uids)->get();
		}else{
			return DB::table('account')->where('uid','=',$uids)->first();
		}
	}
	
    /**
	 * 
	 */
	public static function getUserInfo($identify,$identify_field = 'uid')
	{
		$fields = array('uid','nickname','email','avatar','mobile','sex','birthday','dateline','summary','homebg');
		$user = DB::table('account')->select($fields)->where($identify_field,'=',$identify)->first();
		//$group = self::getUserGroupView($user['uid']);
		//$user['groups'] = $group['groups'];
		//$user['authorize_nodes'] = $group['authorize'];
		return $user;
	}
	
	public static function getUidListByNickname($nickname)
	{
		if(is_string($nickname)){
			return DB::table('account')->where('nickname','=',$nickname)->lists('uid');
		}elseif(is_array($nickname) && !empty($nickname)){
			return DB::table('account')->whereIn('nickname',$nickname)->lists('uid');
		}
		return null;
	}
	
	/**
	 * 获取用户全部信息
	 * @param int $uid 
	 * @return array $user 用户基本信息+用户组信息+用户权限信息
	 */
	public static function getUserFullInfo($uid)
	{
		$fields = array('uid','nickname','email','avatar','mobile','sex','summary','homebg','birthday','dateline');
		$user = DB::table('account')->select($fields)->where('uid','=',$uid)->first();
		if(!$user) return null;
		$group = self::getUserGroupView($uid);
		$credit = self::getUserCredit($uid);
		$user['score'] = isset($credit['score']) ? $credit['score'] : 0;
		$user['experience'] = isset($credit['experience']) ? $credit['experience'] : 0;
		$user['groups'] = $group['groups'];
		$user['authorize_nodes'] = $group['authorize'];
		return $user;
	}
	
    /**
	 * 获取用户全部信息
	 * @param int $uid 
	 * @return array $user 用户基本信息+用户组信息+用户权限信息
	 */
	public static function getUserFullInfoList($uids)
	{
		$fields = array('uid','nickname','email','avatar','mobile','sex','birthday','summary','homebg','dateline');
		$users = DB::table('account')->select($fields)->whereIn('uid',$uids)->get();
		$credits = self::getUserCreditByUids($uids);
		foreach($users as $key=>$user){
			//$group = self::getUserGroupView($user['uid']);
			//$credit = self::getUserCredit($user['uid']);
		    $users[$key]['score'] = isset($credits[$user['uid']]) ? $credits[$user['uid']]['score'] : 0;
		    $users[$key]['experience'] = isset($credits[$user['uid']]) ? $credits[$user['uid']]['experience'] : 0;
		    //$users[$key]['groups'] = $group['groups'];
		    //$users[$key]['authorize_nodes'] = $group['authorize'];
		}
		
		return $users;
	}
	/**
	 * 
	 * Enter description here ...
	 */
	public static function getCreditLevel()
	{
		$cachekey = 'credit::credit_level';
		$cache = null;//CacheService::get($cachekey);
		if(!$cache){
		    $cache = DB::table('credit_level')->orderBy('start','asc')->get();
		    //CacheService::put($cachekey,$cache,30);
		}
		return $cache;
	}
	
    /**
	 * 获取用户权限视图
	 */
	public static function getUserGroupView($uid)
	{
		$auth = array('groups'=>array(),'authorize'=>array());
		$group_ids = DB::table('account_group_link')->where('uid','=',$uid)->lists('group_id');
		if(empty($group_ids)) return $auth;
		$groups = DB::table('account_group')->whereIn('group_id',$group_ids)->get();				
		foreach($groups as $key=>$group){			
			if(!empty($group['authorize_nodes'])){
				$auth['authorize'] += unserialize($group['authorize_nodes']);
			}
			unset($group['authorize_node']);
			$auth['groups'][$group['group_id']] = $group;
		}		
		unset($groups);
		return $auth;
	}
	
	/**
	 * 获取用户积分
	 */
	public static function getUserCredit($uid)
	{
		return DB::table('credit_account')->where('uid','=',$uid)->first();
	}
	
	public static function getUserCreditByUids($uids)
	{
		$users = DB::table('credit_account')->whereIn('uid',$uids)->get();
		$out = array();
		foreach($users as $one)
		{
			$out[$one['uid']] = $one;
		}
		return $out;
	}
	
    /**
	 * 获取积分历史记录
	 */
	public static function getCreditHistory($uid,$page=1,$pagesize=10)
	{
		$res = DB::table('account_credit_history')
		           ->where('uid','=',$uid)
		           ->orderBy('mtime','desc')
		           ->forPage($page,$pagesize)
		           ->get();
		return $res;
	}
	
	/**
	 * 积分处理
	 */
    public static function doUserCredit($uid,$action,$info='')
	{
		$creditlist = DB::table('credit_setting')->orderBy('id','asc')->get();
		$list = array();
		foreach($creditlist as $credit){
			$list[$credit['name']] = $credit;
		}
		unset($creditlist);
		if(!isset($list[$action])){
			return false;
		}
		$action_name = $list[$action]['alias'];
		$score = $list[$action]['score'];
		$experience = $list[$action]['experience'];
		
		//规则检测
		$crcletype = $list[$action]['crcletype'];
		$rewardnum = (int)$list[$action]['rewardnum'];
		
		if($rewardnum>0){
			
			if($crcletype==1){//每日
				$start = mktime(0,0,0,date('m'),date('d'),date('Y'));
				
			}elseif($crcletype==2){//每周
				$days = date('N')-1;				
				$start = mktime(0,0,0,date('m'),date('d')-$days,date('Y'));
			}elseif($crcletype==3){//每月
				$start = mktime(0,0,0,date('m'),1,date('Y'));
			}else{
				$start = 0;
			}
			
			$total = DB::table('account_credit_history')
			             ->where('uid','=',$uid)
			             ->where('action','=',$action)
			             ->where('mtime','>=',$start)
			             ->count();
			if($total>=$rewardnum) return null;
		}
		
		$userCredit = DB::table('credit_account')->where('uid','=',$uid)->first();
		if($userCredit){
			//$data['score'] = $userCredit['score']+$score;
			//$data['experience'] = $userCredit['experience'] + $experience;
			//DB::table('credit_account')->where('uid','=',$uid)->update($data);
			$score!=0 && self::dbClubMaster()->table('credit_account')->where('uid','=',$uid)->increment('score',$score);
			$experience !=0 && self::dbClubMaster()->table('credit_account')->where('uid','=',$uid)->increment('experience',$experience);
		}else{
			$data['score'] = $score;
			$data['experience'] = $experience;
			$data['uid'] = $uid;
			DB::table('credit_account')->insert($data);
		}
		if($score>0){
			$sign = '增加';
		}else{
			$sign = '减少';
		}
		//
		$info_rule = array('{action}'=>$action_name,'{sign}'=>$sign,'{score}'=>$score,'{typecn}'=>'游币','{experience}'=>$experience);
		if(!empty($list[$action]['info'])){
			$info = str_replace(array_keys($info_rule),array_values($info_rule),$list[$action]['info']);
		}
		if($score){
			$credit_history = array('uid'=>$uid,'info'=>$info,'action'=>$action,'type'=>'游币','credit'=>$score,'mtime'=>(int)microtime(true));
			DB::table('account_credit_history')->insert($credit_history);
		}
		return true;
	}
	
	/**
	 * 验证登录
	 */
    public static function verifyLocalLogin($identify,$password,$identify_field = 'email')
	{
		$user = DB::table('account')->where($identify_field,'=',$identify)->first();
		if($user && isset($user['password'])){
			if($user['password']==self::cryptPwd($password)){		
				return $user;
			}else{
				return -1;
			}
		}else{
			return null;
		}		
	}
	
	/**
	 * 修改密码
	 * 
	 */	
	public static function modifyAccountPassword($uid,$password)
	{
		$row = DB::table('account')->where('uid','=',$uid)->update(array('password'=>self::cryptPwd($password)));
		if($row){
			return true;
		}
		return false;
	}
	
	/**
	 * 修改基本信息
	 */
	public static function modifyAccountInfo($uid,$info=null,$group_ids=null)
	{
		$row_1 = $row_2 = false;
		if($info){
			$account = array();
			$fields = array('nickname','sex','mobile','birthday','summary','avatar','homebg');
		     foreach($info as $field=>$data){
				if(in_array($field,$fields)){
					$account[$field] = $data;
				}
			}
			if($account){
				if(isset($account['nickname'])){
					
				    if(!$account['nickname'] || empty($account['nickname'])){
						unset($account['nickname']);
					}else{
						$count = DB::table('account')->where('uid','<>',$uid)->where('nickname','=',$account['nickname'])->count();
						if($count){
							return -1;
						}
					}
					
				}					
			    $row_1 = DB::table('account')->where('uid','=',$uid)->update($account);
			}
		}
		if($group_ids){
			DB::table('account_group_link')->where('uid','=',$uid)->delete();
			if(!is_array($group_ids)) $group_ids = array($group_ids);
			$data = array();			
			foreach($group_ids as $group_id){
				$data[] = array('uid'=>$uid,'group_id'=>$group_id);
			}
			DB::table('account_group_link')->insert($data);
			$row_2 = true;
		}
		//$row = 
		if($row_1 || $row_2){
			return true;
		}
		return false;
	}
	
    /**
	 * 更新用户邮箱
	 */
	public static function modifyAccountEmail($uid,$email)
	{
		$count = DB::table('account')->where('uid','<>',$uid)->where('email','=',$email)->count();
		if($count){
			return -1;
		}
		$rows = DB::table('account')->where('uid','=',$uid)->update(array('email'=>$email));
		if($rows>0){
			
		}
		return $rows;
		
	}
	
	/**
	 * 更新用户昵称
	 */
	public static function modifyAccountNickname($uid,$nickname)
	{
		$count = DB::table('account')->where('uid','<>',$uid)->where('nickname','=',$nickname)->count();
		if($count){
			return -1;
		}
		$rows = DB::table('account')->where('uid','=',$uid)->update(array('nickname'=>$nickname));
		return $rows;
	}
	
	/**
	 * 更新用户头像
	 */
	public static function modifyAccountAvatar($uid, $avatar)
	{
		$row = DB::table('account')->where('uid','=',$uid)->update(array('avatar'=>$avatar));
		if($row){
			return true;
		}
		return false;
	}
	
    /**
	 * 更新背景
	 */
	public static function modifyHomeBg($uid, $bg)
	{
		$row = DB::table('account')->where('uid','=',$uid)->update(array('homebg'=>$bg));
		if($row){
			return true;
		}
		return false;
	}
	
	/**
	 * 屏蔽昵称或头像
	 */
    public static function shieldAccountField($uid,$field,$data)
	{
		if(!in_array($field,array('nickname','avatar'))) return false;
		$user = DB::table('account')->where('uid','=',$uid)->first();
		$log = array('uid'=>$uid,'field'=>$field,'data'=>$user[$field],'ctime'=>(int)microtime(true));
		DB::table('account_shield_history')->insertGetId($log);
		DB::table('account')->where('uid','=',$uid)->update(array($field=>$data));
		return true;
	}
	
	/**
	 * 获取用户主页设置
	 */
	public static function getUserPage($uid)
	{
		return DB::table('account_page')->where('uid','=',$uid)->first();
	}
	
	/**
	 * 保存用户主页设置
	 */
	public static function saveUserPage($data)
	{
		if(!isset($data['uid']) || empty($data['uid'])) return false;
		$uid = $data['uid'];		
		$count = DB::table('account_page')->where('uid','=',$uid)->count();
		if($count>0){
			unset($data['uid']);
			return DB::table('account_page')->where('uid','=',$uid)->update($data);
		}else{
			DB::table('account_page')->insertGetId($data);
		}
	}
	/*
	 * 密码的加密算法
	 */
	protected static function cryptPwd($password)
	{
		$salt = md5(substr($password,-1));
		$password = md5($password . $salt);
		return $password;
	}
}