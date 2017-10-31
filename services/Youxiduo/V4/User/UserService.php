<?php
/**
 * @package Youxiduo
 * @category Base 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\V4\User;

use Youxiduo\V4\User\Model\Relation;

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Youxiduo\User\Model\Account;
use Youxiduo\User\Model\AccountSession;
use Youxiduo\User\Model\UserMobile;
use Youxiduo\V4\User\Model\ThirdAccountLogin;
use Youxiduo\V4\User\Model\Area;
use Youxiduo\V4\User\Model\UserArea;
use Youxiduo\V4\User\Model\StartupInfo;
use Youxiduo\V4\User\Model\InviteRecord;
use Youxiduo\V4\Helper\GeoHash;



class UserService extends BaseService
{
	const ERROR_MOBILE_FORMAT_INVALID = 'mobile_format_invalid';//手机号码格式错误
	const ERROR_EMAIL_FORMAT_INVALID = 'email_format_invalid';//手机号码格式错误
	const ERROR_SMS_VERIFYCODE_FAILURE = 'sms_verifycode_failure';//短信验证码失效
	const ERROR_SMS_VERIFYCODE_ERROR = 'sms_verifycode_error';//短信验证码失效
	const ERROR_MOBILE_EXISTS = 'mobile_exists';//手机号码已经被占用
	const ERROR_MOBILE_NOT_VERIFY = 'mobile_not_verify';//手机号码为验证
	const ERROR_CREATE_USER_ERROR = 'create_user_error';//注册用户失败
	const ERROR_PASSWORD_EMPTY = 'password_empty';//手机号码不能为空
	const ERROR_LOGIN_ERROR = 'login_error';//登录失败
	const ERROR_NICKNAME_EXISTS = 'nickname_exists';//昵称已经存在
	const ERROR_EMAIL_EXISTS = 'email_exists';//邮箱已经存在
	const ERROR_MODIFY_USER_ERROR = 'modify_user_error';//修改用户资料失败
	const ERROR_MODIFY_PASSWORD_ERROR = 'modify_password_error';//修改用户密码失败
	const ERROR_USER_NOT_EXISTS = 'user_not_exists';//用户不存在
	const ERROR_MODIFY_AVATAR_ERROR = 'modify_avatar_error';//修改用户头像失败
	const ERROR_THIRD_NOT_BIND = 'third_not_bind';//第三方未绑定
	
	/**
	 * 发送短信验证码
	 * 
	 * @param string $mobile 手机
	 * @param string $code 验证码
	 * 
	 * @return bool 存在返回true,否则返回false
	 */
	public static function sendVerifyCodeByMobile($mobile,$ip)
	{
		if(Utility::validateMobile($mobile)===true){
			$verifycode = Utility::random(4,'alnum');
			//$verifycode = 1234;
			$result = UserMobile::saveVerifyCodeByPhone($mobile,$verifycode,false,$ip);
			$result==true && Utility::sendVerifySMS($mobile,$verifycode,true);
			return $result;
		}
		return self::ERROR_MOBILE_FORMAT_INVALID;
	}
	
	/**
	 * 检查手机验证码
	 * 
	 * @param string $mobile 手机
	 * @param string $code 验证码
	 * 
	 * @return bool 存在返回true,否则返回false
	 */
	public static function checkVerifyCodeByMobile($mobile,$verifycode)
	{
		if(Utility::validateMobile($mobile)===true && !empty($verifycode)){
			$num = 0;	
			$result = UserMobile::verifyPhoneVerifyCode($mobile,$verifycode,$num);
			if($result===true){
			    return true;
			}else{
				if($num >= 3){
					return self::ERROR_SMS_VERIFYCODE_FAILURE;
				}
				return self::ERROR_SMS_VERIFYCODE_ERROR;
			}
		}
		if(empty($verifycode)) return self::ERROR_SMS_VERIFYCODE_ERROR;
		return self::ERROR_MOBILE_FORMAT_INVALID;
	}
	
	/**
	 * 手机注册
	 * @param string $mobile 手机
	 * @param string $password 密码
	 * 
	 * @return int 成功返回用户的UID,失败返回
	 */
	public static function createUserByMobile($mobile,$password,$params=array())
	{
		if(Utility::validateMobile($mobile)===true && !empty($password)){
			if(Account::isExistsByField($mobile,Account::IDENTIFY_FIELD_MOBILE)===true){
				//return self::trace_error('E1','该手机号已经存在');
				//$uid = self::modifyUserPwd($mobile, $password);
				return self::ERROR_MOBILE_EXISTS;
			}else{
				if(UserMobile::phoneVerifyStatus($mobile)===false) return self::ERROR_MOBILE_NOT_VERIFY;
			    $uid = Account::createUserByPhone($mobile,$password,$params);
			}
			if($uid>0){
				$session = self::makeAccessToken($uid);
				return array('uid'=>$uid,'session_id'=>$session);
			}
			return self::ERROR_CREATE_USER_ERROR;
		}
		if(empty($password)) return self::ERROR_PASSWORD_EMPTY;
		return self::ERROR_MOBILE_FORMAT_INVALID;
	}
	
    /**
	 * 检查手机是否存在
	 * 
	 * @param string $mobile 手机
	 * @param int $uid 排除比较的用户UID
	 * 
	 * @return bool 存在返回true,否则返回false
	 */
	public static function isExistsByMobile($mobile,$uid=0)
	{
	    $res = Account::isExistsByField($mobile,Account::IDENTIFY_FIELD_MOBILE,$uid);
	    if($res==true){
			return self::ERROR_MOBILE_EXISTS;
		}else{
			return true;
		}
	}
	
	/**
	 * 检查昵称是否存在
	 * 
	 * @param string $nickname 昵称
	 * @param int $uid 排除比较的用户UID
	 * 
	 * @return bool 存在返回true,否则返回false
	 */
	public static function isExistsByNickname($nickname,$uid)
	{
	    $res = Account::isExistsByField($nickname,Account::IDENTIFY_FIELD_NICKNAME,$uid);
	    if($res==true){
			return self::ERROR_NICKNAME_EXISTS;
		}else{
			return true;
		}
	}
	
	/**
	 * 通过邮箱或手机登录
	 * @param string $identify 
	 * @param string $password
	 * 
	 * @return array|string $result 成功返回用户的数组,失败返回错误码
	 */
	public static function login($identity,$password)
	{
	    if(Utility::validateEmail($identity)){
			return self::loginByEmail($identity, $password);
		}elseif(Utility::validateMobile($identity)){
			return self::loginByPhone($identity, $password);
		}else{
			return self::ERROR_LOGIN_ERROR;
		}
	}
	
    public static function loginByPhone($mobile,$password)
	{
		if(Utility::validateMobile($mobile)===true && !empty($password)){
			$user = Account::doLocalLogin($mobile,Account::IDENTIFY_FIELD_MOBILE,$password);
			if($user){
				$mobile_verify = UserMobile::phoneVerifyStatus($user['mobile']);
				$session = self::makeAccessToken($user['uid']);
				return array('uid'=>$user['uid'],'mobile_is_verify'=>$mobile_verify,'session_id'=>$session);
			}else{
				return self::ERROR_LOGIN_ERROR;
			}
		}
		if(empty($password)) return self::ERROR_PASSWORD_EMPTY;
		return self::ERROR_MOBILE_FORMAT_INVALID;
	}
	
    public static function loginByEmail($email,$password)
	{
		if(Utility::validateEmail($email)===true && !empty($password)){
			$user = Account::doLocalLogin($email,Account::IDENTIFY_FIELD_EMAIL,$password);
			if($user){
				$mobile_verify = UserMobile::phoneVerifyStatus($user['mobile']);
				$session = self::makeAccessToken($user['uid']);
				return array('uid'=>$user['uid'],'mobile_is_verify'=>$mobile_verify,'session_id'=>$session);
			}else{
				return self::ERROR_LOGIN_ERROR;
			}
		}
		if(empty($password)) return self::ERROR_PASSWORD_EMPTY;
		return self::ERROR_EMAIL_FORMAT_INVALID;
	}
	
	public static function passMobileVerify($uid)
	{
		$user = Account::db()->where('uid','=',$uid)->first();
		if(!$user || !$user['mobile']) return false;
		$mobile_verify = UserMobile::phoneVerifyStatus($user['mobile']);
		return $mobile_verify;
	}
	
	/**
	 * 是否绑定第三方账号
	 * @param string $type
	 * @param string $access_token
	 * @param string $user_id
	 * 
	 * @return array|string
	 */
	public static function isBindThird($type,$access_token,$user_id)
	{
		$types = array('sina'=>1,'qq'=>2,'weixin'=>3);
		if(!isset($types[$type])) return self::ERROR_THIRD_NOT_BIND;
		$type = $types[$type];
		$uid = ThirdAccountLogin::getUserByUserId($type, $user_id);
		if($uid===false) return self::ERROR_THIRD_NOT_BIND;
		$user = Account::getUserInfoByField($uid,Account::IDENTIFY_FIELD_UID);
		if($user){
			$mobile_verify = UserMobile::phoneVerifyStatus($user['mobile']);
			$session = self::makeAccessToken($user['uid']);
			return array('uid'=>$user['uid'],'mobile_is_verify'=>$mobile_verify,'session_id'=>$session);
		}
		return self::ERROR_THIRD_NOT_BIND;
	}
	
	/**
	 * 第三方账号首次登录
	 * @param string $type
	 * @param string $access_token
	 * @param string $user_id
	 * @param array $params
	 * 
	 * @return array|string
	 */
	public static function loginByThird($type,$access_token,$user_id,$nickname,$password,$mobile,$params=array())
	{
		$types = array('sina'=>1,'qq'=>2,'weixin'=>3);
		if(!isset($types[$type])) return self::ERROR_THIRD_NOT_BIND;
		$type = $types[$type];
		$exists = self::isExistsByNickname($nickname,0);
		if($exists !== true) return $exists;
		
		$params['mobile'] = $mobile;
		$uid = Account::createUserByNickname($nickname,$password,$params);
		if(!$uid) return self::ERROR_CREATE_USER_ERROR;
	    $user = Account::getUserInfoByField($uid,Account::IDENTIFY_FIELD_UID);
		if($user){
			ThirdAccountLogin::bindThirdUser($uid, $type, $access_token,$user_id);
			$mobile_verify = UserMobile::phoneVerifyStatus($user['mobile']);
			$session = self::makeAccessToken($user['uid']);
			return array('uid'=>$user['uid'],'mobile_is_verify'=>$mobile_verify,'session_id'=>$session);
		}
		return self::ERROR_CREATE_USER_ERROR;
	}
	
	/**
	 * 修改用户密码
	 * 
	 * @param int $uid
	 * @param string $password
	 * 
	 */
	public static function resetUserPassword($mobile,$verifycode,$password)
	{
		$num = 0;
		$valid = UserMobile::verifyPhoneVerifyCode($mobile,$verifycode,$num);
		if($valid===true){
			$res = Account::modifyUserPwd($mobile,Account::IDENTIFY_FIELD_MOBILE,$password);
			return true;
		}
		return self::ERROR_SMS_VERIFYCODE_ERROR;
	}
	
	/**
	 * 修改用户手机
	 * 
	 * @param int $uid
	 * @param string $mobile
	 */
	public static function modifyUserMobile($uid,$mobile,$verifycode,$password='')
	{
		$num = 0;
		$valid = UserMobile::verifyPhoneVerifyCode($mobile,$verifycode,$num);
		if($valid===true){
			if(!empty($password)){
			    $user = Account::doLocalLogin($uid, Account::IDENTIFY_FIELD_UID, $password);
			    if(!$user) return self::ERROR_LOGIN_ERROR;
			}
			$data = array('mobile'=>$mobile);
			$res = Account::modifyUserInfo($uid, $data);
			return true;			
		}
		return self::ERROR_SMS_VERIFYCODE_ERROR;
	}
	
	/**
	 * 修改用户资料
	 * 
	 * @param int $uid
	 * @param array $info
	 */
	public static function modifyUserInfo($uid,$input)
	{		
		$fields = array('nickname','email','summary','birthday','sex','mobile','avatar','homebg');
		$data = array();
		//过滤非法字段
		foreach($fields as $field){
			isset($input[$field]) && $input[$field]!==null && $data[$field] = $input[$field];
		}
		//验证昵称唯一性
		if(isset($data['nickname']) && $data['nickname']){
			if(Account::isExistsByField($data['nickname'],Account::IDENTIFY_FIELD_NICKNAME,$uid)===true){
				//昵称已经存在
				return self::ERROR_NICKNAME_EXISTS;
			}
		}        
		//验证手机唯一性
	    if(isset($data['mobile']) && $data['mobile']){
			if(Account::isExistsByField($data['mobile'],Account::IDENTIFY_FIELD_NICKNAME,$uid)===true){
				//手机号码已经存在
				return self::ERROR_MOBILE_EXISTS;
			}
		}
	    //验证手机唯一性
	    if(isset($data['email']) && $data['email']){
			if(Account::isExistsByField($data['email'],Account::IDENTIFY_FIELD_EMAIL,$uid)===true){
				//手机号码已经存在
				return self::ERROR_MOBILE_EXISTS;
			}
		}
        if($data){
		    $res = Account::modifyUserInfo($uid, $data);
        }else{
        	return self::ERROR_MODIFY_USER_ERROR;
        }
        
		if($res){
			return true;
		}else{
			return true;//self::ERROR_MODIFY_USER_ERROR;
		}
	}
	
	/**
	 * 修改用户头像
	 * 
	 * @param int $uid
	 * @param string $avatar
	 */
	public static function modifyUserAvatar($uid,$avatar)
	{
		$data = array('avatar'=>$avatar);
		$res = Account::modifyUserInfo($uid, $data);
	    if($res){
			return true;
		}else{
			return self::ERROR_MODIFY_AVATAR_ERROR;
		}
	}
	/**
	 * 修改用户密码
	 * 
	 * @param int $uid
	 * @param string $password
	 * 
	 */
	public static function modifyUserPassword($uid,$password)
	{
	    $res = Account::modifyUserPwd($uid,Account::IDENTIFY_FIELD_UID,$password);
		if($res){
			return true;
		}else{
			return self::ERROR_MODIFY_PASSWORD_ERROR;
		}
	}
	
	public  static function getUserIdByMobile($mobile)
	{
		$user = Account::getUserInfoByField($mobile,'mobile');
		if($user){
			return $user['uid'];
		}
		return 0;
	}
	
	/**
	 * 通过UID获取用户信息
	 * 
	 * @param int $uid
	 */
	public static function getUserInfoByUid($uid,$filter='basic',$compare_uid=0)
	{
		$user = Account::getUserInfoById($uid,$filter);
		if(!$user) return self::ERROR_USER_NOT_EXISTS;
		if($compare_uid){
			$user['attention'] = Relation::isAttention($compare_uid,$uid);
			$user['fans'] = Relation::isAttention($uid,$compare_uid);
		}else{
			$user['attention'] = false;
			$user['fans'] = false;
		}
		return $user;
	}
	
	/**
	 * 通过UID获取多个用户信息
	 * 
	 * @param array $uids
	 */
	public static function getMultiUserInfoByUids(array $uids,$filter='basic',$compare_uid=0)
	{
		$users = Account::getMultiUserInfoByUids($uids,$filter);
		if(!$users) return self::ERROR_USER_NOT_EXISTS;
		
		if($compare_uid){
			$attention_uids = Relation::getAllAttention($compare_uid);
			$fans_uids = Relation::getAllFans($compare_uid);
			foreach($users as $key=>$row){
				if($attention_uids && in_array($row['uid'],$attention_uids)){
					$row['attention'] = true;
				}else{
					$row['attention'] = false;
				}
			    if($fans_uids && in_array($row['uid'],$fans_uids)){
					$row['fans'] = true;
				}else{
					$row['fans'] = false;
				}
				$users[$key] = $row;
			}
		}
		
		return $users;
	}
	
	/**
	 * 附近的人
	 */
	public static function getNearbyUsers($uid,$longitude,$latitude,$distance,$pageIndex,$pageSize,$nickname)
	{
		$search['uid'] = 123279;
		$search = array();
		if($nickname){
			//$search['nickname'] = $nickname;
		}
		/*
		$squares = self::getSquarePoint($longitude,$latitude);
		if($squares){
			$search['right_bottom_lat'] = $squares['right_bottom']['lat'];
			$search['left_top_lat'] = $squares['left_top']['lat'];
			$search['left_top_long'] = $squares['left_top']['lng'];
			$search['right_bottom_long'] = $squares['right_bottom']['lng'];
		}
		*/
		
		if($longitude && $latitude){
			$geohash = GeoHash::encode($latitude,$longitude);			
			//$search['geohash'] = substr($geohash,0,5);
		}
		$total = Account::searchCount($search);		
		$result = Account::searchList($search,$pageIndex,$pageSize);
		if($total==0 || !$result) return array('result'=>array(),'totalCount'=>0); 		
		$out = array();
		foreach($result as $row){
			$user = Account::filterUserFields($row,'basic');
			$out[] = $user;
		}
		return array('result'=>$out,'totalCount'=>$total);
	}
	
	public static function formatDataToKey($data,$key)
	{
		if(!$data || !is_array($data)) return array();
		$out = array();
		foreach($data as $row){
			$out[$row[$key]] = $row;
		}
		return $out;
	}
	
    protected static function makeAccessToken($uid,$client='android')
	{
		$session = AccountSession::makeSession();
		$id = AccountSession::saveSession($uid,$session,$client);
		if($session && $id) return $session;
		return '';
	}
	
	public static function getUidFromSession($session_id)
	{
		return AccountSession::getUidFromSession($session_id);
	}
	
	public static function getSessionFromUid($uid)
	{
		return AccountSession::getSessionFromUid($uid);
	}
	
	/**
	 * 
	 */
	public static function searchByUserName($username,$pageIndex=1,$pageSize=10,$compare_uid=0,$platform='ios',$order=array())
	{
		$users = Account::searchUserByNickname($username,$pageIndex,$pageSize);
	    if($compare_uid){
			$attention_uids = Relation::getAllAttention($compare_uid);
			$fans_uids = Relation::getAllFans($compare_uid);
			foreach($users as $key=>$row){
				if($attention_uids && in_array($row['uid'],$attention_uids)){
					$row['attention'] = true;
				}else{
					$row['attention'] = false;
				}
			    if($fans_uids && in_array($row['uid'],$fans_uids)){
					$row['fans'] = true;
				}else{
					$row['fans'] = false;
				}
				$users[$key] = $row;
			}
		}
		return $users;
	}
	
	public static function getArea($id,$type)
	{
		$result = null;
		//return Area::getAllArea();
		if($type=='province') $result = Area::getProvinceToKeyValue();
		if($type=='city') $result = Area::getCityToKeyValue($id);
		if($type=='region') $result = Area::getRegionToKeyValue($id);
		if($result===null) return 'area_not_exists';
		return $result;
	}
	
	public static function getUserArea($uids)
	{
		if(strpos($uids,',')!==false){
		    $uids = explode(',',$uids);
		    if($uids){
		    	$area = UserArea::db()->whereIn('uid',$uids)->get();
		    	return $area;
		    }
		    return array();
		}else{			
		    $uid = (int)$uids;
		    $area = UserArea::db()->where('uid','=',$uid)->first();
		    return $area ? : (object)array();
		}		
	}
	
	public static function updateUserArea($uid,$data)
	{
		$exists = UserArea::db()->where('uid','=',$uid)->first();
		$data['country'] = '中国';
		if($exists){
			$data['updatetime'] = time();
			UserArea::db()->where('uid','=',$uid)->update($data);
		}else{
			$data['uid'] = $uid;
			$data['updatetime'] = time();			
			UserArea::db()->insert($data);
		}
		return true;
	}
	
	public static function inviteUser($uid,$inviteCode)
	{
		if(!$uid || !$inviteCode) return false;
		$inviter = Account::db()->where('zhucema','=',$inviteCode)->first();
		if(!$inviter) return false;
		$inviter_uid = $inviter['uid'];
		$data = array('oldid'=>$inviter_uid,'newid'=>$uid,'ctime'=>time());
		$id = InviteRecord::db()->insert($data);
		return $id ? true : false;
	}
	
	public static function inviteUserCount($search)
	{
		$total = InviteRecord::findCount($search);
		return $total;
	}
	
	public static function inviteUserList($search,$pageIndex=1,$pageSize=10)
	{
		$total = InviteRecord::findCount($search);
		$result = InviteRecord::findList($search,$pageIndex,$pageSize);
		if($total==0 || !$result) return array('result'=>array(),'totalCount'=>0); 
		$uids = array();
		foreach($result as $row){
			$uids[] = $row['newid'];
		}
		if(!$uids) return array('result'=>array(),'totalCount'=>0);
		$users = self::formatDataToKey(self::getMultiUserInfoByUids($uids),'uid');
		$out = array();
		foreach($result as $row){
			if(!isset($users[$row['newid']])) continue;
			$out[] = $users[$row['newid']];
		}
		return array('result'=>$out,'totalCount'=>$total);
	}
	
	public static function findMyInviter($uid)
	{
		$inviter_uid = InviteRecord::findMyInviter($uid);
		$user = self::getUserInfoByUid($inviter_uid);
		return $user;
	}
	
	public static function inviteRank($search,$pageIndex=1,$pageSize=10,$uid=0)
	{
		$rows = InviteRecord::rankList($search,$pageIndex,$pageSize);		
		$users = array();
		$uids = array();
		$out = array();
		foreach($rows as $row){
			$uids[] = $row['uid'];
		}
		$users = self::getMultiUserInfoByUids($uids,'short');
		if($users){
			$users = self::formatDataToKey($users,'uid');
		}
		foreach($rows as $row){
			if(!isset($users[$row['uid']])) continue;
			$user = $users[$row['uid']];
			$user['number'] = $row['total'];
			$out[] = $user;
		}
		return $out;
	}
	
	/**
	 * 记录最后启动信息
	 */
	public static function recordStartupInfo($uid,$apple_token,$idfa,$mac,$idcode,$os,$osversion,$ip,$longitude,$latitude,$platform)
	{
		$data = array();
		$data['uid'] = $uid;
		$data['apple_token'] = $apple_token;
		$data['idfa'] = $idfa;
		$data['mac'] = $mac;
		$data['idcode'] = $idcode;
		$data['os'] = $os;
		$data['osversion'] = $osversion;
		$data['ip'] = $ip;
		$data['longitude'] = $longitude;
		$data['latitude'] = $latitude;
		$data['platform'] = $platform;
		$data['create_time'] = date('Y-m-d H:i:s');
		//$rows = StartupInfo::db()->insert($data);
		if($uid && $longitude && $latitude){
			$geohash = GeoHash::encode($latitude,$longitude);
			Account::db()->where('uid','=',$uid)->update(array('longitude'=>$longitude,'latitude'=>$latitude,'geohash'=>$geohash));
		}
		
		if($uid && $apple_token){
			Account::db()->where('uid','=',$uid)->update(array('apple_token'=>$apple_token));
		}
		
		return true;
	}
	
    public static function matchingUserByMobile($mobiles,$compare_uid)
	{
		if(!$mobiles) return array();
		$users = Account::matchingUserByMobile($mobiles);
		$attention_uids = array();
		$fans_uids = array();
		if($compare_uid){
			$attention_uids = Relation::getAllAttention($compare_uid);
			$fans_uids = Relation::getAllFans($compare_uid);
		}
		
		$out = array();
	    foreach($users as $user)
		{
			$user['avatar'] = $user['avatar'] ? Utility::getImageUrl($user['avatar']) : '';
		    if($attention_uids && in_array($user['uid'],$attention_uids)){
				$user['attention'] = true;
			}else{
				$user['attention'] = false;
			}
		    if($fans_uids && in_array($user['uid'],$fans_uids)){
				$user['fans'] = true;
			}else{
				$user['fans'] = false;
			}
			$out[] = $user;
		}
		return $out;
	}
	
	public static function getSquarePoint($lng, $lat,$distance = 0.5)
	{
		if(!$lat || !$lng) return null;
		$earth_radius = 6371;
		$dlng =  2 * asin(sin($distance / (2 * $earth_radius)) / cos(deg2rad($lat)));
	    $dlng = rad2deg($dlng);	     
	    $dlat = $distance/$earth_radius;
	    $dlat = rad2deg($dlat);
     
        return array(
            'left_top'=>array('lat'=>$lat + $dlat,'lng'=>$lng-$dlng),
            'right_top'=>array('lat'=>$lat + $dlat, 'lng'=>$lng + $dlng),
            'left_bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng - $dlng),
            'right_bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng + $dlng)
        );
	}
	
	public static function isNewUser($uid)
	{
		return Account::db()->where('uid','=',$uid)->pluck('is_first');
	}
}
