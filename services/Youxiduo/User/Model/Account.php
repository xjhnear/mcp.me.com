<?php
/**
 * @package Youxiduo
 * @category Android 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\User\Model;

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

use Youxiduo\User\Model\AccountAdmin;
use Youxiduo\Helper\Utility;
/**
 * 账号模型类
 */
final class Account extends Model implements IModel
{	
	const IDENTIFY_FIELD_UID      = 'uid';
	const IDENTIFY_FIELD_EMAIL    = 'email';
	const IDENTIFY_FIELD_MOBILE   = 'mobile';
	const IDENTIFY_FIELD_NICKNAME = 'nickname';
	const IDENTIFY_FIELD_IDFA = 'idfa';
	
	
    public static function getClassName()
	{
		return __CLASS__;
	}	
	
	/**
	 * 创建用户通过手机号
	 */
	public static function createUserByPhone($mobile,$password,$params=array(),$platform='android',$client='android',$idfa='')
	{
		$data = array();
		$data['mobile'] = $mobile;
		$data['password'] = Utility::cryptPwd($password);
		$data['dateline'] = time();
		if ($client == 'glwzry') {
		    $data['client'] = $client;
		} else {
		    $data['client'] = $platform;
		}
		$data['idfa'] = $idfa;
		$data['zhucema'] = Utility::makeInvitationCode();	
		$data['reg_ip'] = isset($params['ip']) ? $params['ip'] : '';
		$data['idcode'] = isset($params['idcode']) ? $params['idcode'] : '';
		$avatar = '/userdirs/common/android/avatars/'.rand(1,20).'.jpg';
		$data['avatar'] = $avatar;
		$uid = self::db()->insertGetId($data);
		self::db()->where('uid','=',$uid)->update(array('nickname'=>'玩家'.$uid));
		return $uid;	
	}
	
	/**
	 * 创建用户通过邮箱
	 */
	public static function createUserByEmail($email,$password,$params=array())
	{
		$data = array();
		$data['email'] = $email;
		$data['password'] = Utility::cryptPwd($password);
		$data['dateline'] = time();
		$data['client'] = '';
		$data['zhucema'] = Utility::makeInvitationCode();	
		$uid = self::db()->insertGetId($data);
		return $uid;
	}
	
	public static function createUserByNickname($nickname,$password,$params=array())
	{
		$data = array();
		$data['nickname'] = $nickname;
		$data['password'] = Utility::cryptPwd($password);
		$data['dateline'] = time();
		$data['client'] = '';
		$data['zhucema'] = Utility::makeInvitationCode();	
		isset($params['mobile']) && $data['mobile'] = $params['mobile'];
		isset($params['email']) && $data['email'] = $params['email'];
		isset($params['avatar']) && $data['avatar'] = $params['avatar'];
		$uid = self::db()->insertGetId($data);
		return $uid;
	}
	
	public static function isExistsByField($identify,$identify_field,$uid=0,$count=false)
	{
		if(!in_array($identify_field,array('mobile','email','nickname','idfa'))) return true;
		$tb = self::db()->where($identify_field,'=',$identify);
		if($uid){
			$tb = $tb->where('uid','!=',$uid);
		}
		if ($count == true) {
		    $user = $tb->count();
		    return $user;
		}
		$user = $tb->first();
		return $user ? true : false;
	}
	
	/**
	 * 账号登录
	 */
	public static function doLocalLogin($identify,$identify_field,$password)
	{
		if(!in_array($identify_field,array('uid','mobile','email','vuser'))) return false;
		if(strlen($password) != 32){
			$password = Utility::cryptPwd($password);
		}
		$user = self::db()->where($identify_field,'=',$identify)->where('password','=',$password)->first();
		
		return $user;
	}

    /**
     * 获取用户信息
     * @param $uid
     * @param string $filter
     * @return array
     */
	public static function getUserInfoById($uid,$filter='info',$appname=NULL)
	{
		$info = self::db()->where('uid','=',$uid);
		if ($appname) {
		    $info = $info->where('client',$appname);
		}
		$info = $info->first();
		if(!$info) return null;
		$info && $info['avatar'] = Utility::getImageUrl($info['avatar']);
		$info && $info['homebg'] = Utility::getImageUrl($info['homebg']);
		$info && $info['dateline'] = date('Y-m-d H:i:s',$info['dateline']);
		$info && $info['admin_lv'] = AccountAdmin::isAdmin($uid)?1:0;
		return self::filterUserFields($info,$filter);
	}
	
	public static function getMultiUserInfoByUids(array $uids,$filter='info',$appname=NULL)
	{
		if(!$uids) return array();
		$users = self::db()->whereIn('uid',$uids);
		if ($appname) {
		    $users = $users->where('client',$appname);
		}
		$users = $users->get();
		foreach($users as $key=>$info){
			$info['avatar'] = Utility::getImageUrl($info['avatar']);
			$info['homebg'] = Utility::getImageUrl($info['homebg']);
			$info['dateline'] = date('Y-m-d H:i:s',$info['dateline']);
			$users[$key] = self::filterUserFields($info,$filter);
		}
		return $users;
	}
	
	public static function getMultiUserInfolist($filter='info',$pageIndex,$pageSize,$hastoken,$appname=NULL)
	{
	    $tb = self::db();
	    if($hastoken){
	        $tb = $tb->where('apple_token','<>','');
	    }
	    if($appname){
	        $tb = $tb->where('client',$appname);
	    }
	    if($pageIndex && $pageSize){
	        $tb = $tb->forPage($pageIndex,$pageSize);
	    }
	    $users = $tb->get();
	    foreach($users as $key=>$info){
	        $info['avatar'] = Utility::getImageUrl($info['avatar']);
	        $info['homebg'] = Utility::getImageUrl($info['homebg']);
	        $info['dateline'] = date('Y-m-d H:i:s',$info['dateline']);
	        $users[$key] = self::filterUserFields($info,$filter);
	    }
	    return $users;
	}
	
	/**
	 * 
	 */
	public static function getUserInfoByField($identify,$identify_field)
	{
		if(!in_array($identify_field,array('mobile','email','uid','zhucema','nickname'))) return false;
		$user = self::db()->where($identify_field,'=',$identify)->first();
		
		return $user;
	}

    /**
     * @param array $identify
     * @param $identify_field
     * @return bool
     */
    public static function getMultiUserInfoByField($identify=array(),$identify_field)
	{
		if(!in_array($identify_field,array('mobile','email','uid')) || !$identify) return false;
		return self::db()->select('uid','email','nickname','mobile','avatar')->whereIn($identify_field,$identify)->get();
	}
	
	/**
	 * 修改资料
	 */
	public static function modifyUserInfo($uid,$data)
	{
		$res = self::db()->where('uid','=',$uid)->update($data);
		return $res;
	}
	
	/**
	 * 修改密码
	 */
	public static function modifyUserPwd($identify,$identify_field,$password)
	{
		if(!in_array($identify_field,array('mobile','email','uid'))) return false;
		$user = self::getUserInfoByField($identify, $identify_field);
		if($user){
			if(strlen($password)!=32){
				$password = Utility::cryptPwd($password);
			}

		    $res = self::db()->where($identify_field,'=',$identify)->update(array('password'=>$password));
		    return $user['uid'];
		}
		return false;
	}
	
    /**
	 * 过滤用户隐私信息
	 * @param array $user 用户信息
	 * @param string|array 过滤器,默认值:short
	 * 根据不同的需求显示用户字段的信息不同
	 */
	public static function filterUserFields($user,$filter='short')
	{
		if(!$user) return $user;
		//默认的fields的字段列表是全部的字段
		$fields = array(
		    'uid','nickname','avatar',
		    'email','mobile','sex','birthday','summary','homebg','score','experience','dateline','reg_ip',
		    'apple_token','idfa','mac','openudid','osversion','zhucema','province','city','region','is_open_android_money',
		    'groups','authorize_nodes','vuser'
            ,'alipay_num','alipay_name','admin_lv'
		);
		
		if(is_string($filter)){
			if($filter === 'short'){
				$fields = array('uid','nickname','avatar');
			}elseif($filter === 'info'){
				$fields = array('uid','nickname','avatar','email','mobile','sex','birthday','summary','zhucema','province','city','region','is_open_android_money','alipay_num','alipay_name','admin_lv');
			}elseif($filter === 'basic'){
				$fields = array(
				    'uid','nickname','avatar','summary','homebg','sex','mobile',
				    'score','experience','dateline','reg_ip','is_first',
				    'apple_token','idfa','mac','openudid','osversion','zhucema','province','city','region','is_open_android_money',
				    'groups','vuser','alipay_num','alipay_name','admin_lv'
				);
			}
		}		
		$out = array();
		//检测获取到的用户的字段是否在$fields中，如果存在的话，把这个字段存入$out数组中，然后销毁$user数组，返回$out这个数组
		foreach($user as $field=>$value){
			if(in_array($field,$fields)){
				$out[$field] = $value;
			}
		}
		unset($user);
		return $out;
	}
	
	/**
	 * 通过昵称搜索用户
	 * @param string $nickname 昵称
	 */
	public static function searchUserByNickname($nickname,$pageIndex=1,$pageSize=10,$filter='basic')
	{
		$fields = array('uid','nickname','avatar','summary','dateline');
		$users = self::buildSearchByNickname($nickname)
			->select($fields)			
			->orderBy('vuser','asc')
			->orderBy('nickname','asc')
			->forPage($pageIndex,$pageSize)
			->get();
	    foreach($users as $key=>$info){
			$info['avatar'] = Utility::getImageUrl($info['avatar']);
			$info['dateline'] = date('Y-m-d H:i:s',$info['dateline']);
			$users[$key] = self::filterUserFields($info,$filter);
		}
		return $users;
	}
	
	public static function searchUserCountByNickname($nickname)
	{
		return self::buildSearchByNickname($nickname)->count();
	}
	
	protected static function buildSearchByNickname($nickname)
	{
		return self::db()->where(function($query)use($nickname){
			    $query = $query->where('nickname','=',$nickname)->orWhere('nickname','like','%'.$nickname.'%');
			})
			->where('vuser','=',0);
	}
	
	public static function matchingUserByMobile($mobiles)
	{
		$fields = array('uid','nickname','avatar','mobile','sex');
		$users = self::db()
			->select($fields)
			->where('mobile','!=','')
			->whereIn('mobile',$mobiles)
			->where('vuser','=',0)
			->orderBy('vuser','asc')->orderBy('uid','asc')
			->get();
		
		return $users;
	}
	
	public static function searchCount($search)
	{
		return self::buildSearch($search)->count();
	}
	
	public static function searchList($search,$pageIndex,$pageSize,$order=array())
	{
		$tb = self::buildSearch($search);
		foreach($order as $field=>$sort){
			$tb = $tb->orderBy($field,$sort);
		}
		if($pageIndex && $pageSize){
			$tb = $tb->forPage($pageIndex,$pageSize);
		}
		$users = $tb->get();
		foreach($users as $key=>$info){
			$info['avatar'] = Utility::getImageUrl($info['avatar']);
			$info['dateline'] = date('Y-m-d H:i:s',$info['dateline']);
			$users[$key] = self::filterUserFields($info,'basic');
		}
		return $users;
	}
	
	protected static function buildSearch($search)
	{
		$tb = self::db();
		if(isset($search['uid'])){
			$tb = $tb->where('uid','=',$search['uid']);
		}
		
	    if(isset($search['nickname'])){
			$tb = $tb->where('nickname','like','%'.$search['nickname'].'%');
		}
	    if(isset($search['email'])){
			$tb = $tb->where('email','=',$search['email']);
		}
		
	    if(isset($search['phone'])){
			$tb = $tb->where('phone','=',$search['phone']);
		}
		
	    if(isset($search['mobile'])){
			$tb = $tb->where('mobile','=',$search['mobile']);
		}
		
		if(isset($search['geohash']) && $search['geohash']){
			$tb = $tb->where('geohash','like',$search['geohash'].'%');
		}
		
		if(isset($search['right_bottom_lat']) && $search['right_bottom_lat']){
			$tb = $tb->where('latitude','>',$search['right_bottom_lat']);
		}
	    if(isset($search['left_top_lat']) && $search['left_top_lat']){
			$tb = $tb->where('latitude','<',$search['left_top_lat']);
		}
	    if(isset($search['left_top_long']) && $search['left_top_long']){
			$tb = $tb->where('longitude','>',$search['left_top_long']);
		}
	    if(isset($search['right_bottom_long']) && $search['right_bottom_long']){
			$tb = $tb->where('longitude','<',$search['right_bottom_long']);
		}
		return $tb;
	}
}