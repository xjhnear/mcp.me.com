<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

use Youxiduo\V4\Helper\OutUtility;

//登录
Route::any('v4/user/login',array('before'=>'uri_verify',function(){
	$username = Input::get('username');
	$password = Input::get('password');
	$client = Input::get('client');
	$result = Youxiduo\V4\User\UserService::login($username,$password);
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));

//登录签到发背包物品
Route::any('v4/user/sendgoods_by_login',array('before'=>'uri_verify',function(){
	$uid = Input::get('uid');
	$version = Input::get('version');
	$result = Youxiduo\V4\User\UserService::sendgoods_by_login($uid,$version);
	return OutUtility::outSuccess($result);
}));

Route::any('v4/user/check_session',array('before'=>'uri_verify',function(){
    $session_id = Input::get('session_id');
    $client = Input::get('appname');
    $result = Youxiduo\V4\User\UserService::getUidFromSession($session_id,$client);
    return OutUtility::outSuccess($result);
}));

Route::get('v4/user/uid_by_mobile',array('before'=>'uri_verify',function(){
    $mobile = Input::get('mobile');
    $uid = Youxiduo\V4\User\UserService::getUserIdByMobile($mobile);
    return OutUtility::outSuccess(array('uid'=>$uid));
}));

Route::get('v4/user/mobile_by_uid',array('before'=>'uri_verify',function(){
    $uid = Input::get('uid');
    $mobile = Youxiduo\V4\User\UserService::getMobileByUserId($uid);
    return OutUtility::outSuccess($mobile);
}));

Route::get('v4/user/uid_by_invitecode',array('before'=>'uri_verify',function(){
    $invitecode = Input::get('invitecode');
    $uid = Youxiduo\V4\User\UserService::getUserIdByInviteCode($invitecode);
    return OutUtility::outSuccess(array('uid'=>$uid));
}));

//用户是否验证手机
Route::any('v4/user/is_verify_mobile',array('before'=>'uri_verify',function(){
    $uid = Input::get('uid');
    $result = Youxiduo\V4\User\UserService::passMobileVerify($uid);
    return OutUtility::outSuccess($result);
}));
//
Route::any('v4/user/is_bind_third',array('before'=>'uri_verify',function(){
    $type = Input::get('type');    
    $access_token = Input::get('access_token');
    $user_id = Input::get('user_id');
    $result = Youxiduo\V4\User\UserService::isBindThird($type,$access_token,$user_id);
    if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));

//
Route::any('v4/user/third_session',array('before'=>'uri_verify',function(){
    $client = Input::get('client');
    $uid = Input::get('uid');
    
    $result = Youxiduo\V4\User\UserService::Thirdsession($uid,$client);
    if(is_array($result)){
        return OutUtility::outSuccess($result);
    }
    return OutUtility::outError(300,$result);
}));
    
Route::any('v4/user/third_login',array('before'=>'uri_verify',function(){
    $type = Input::get('type');    
    $access_token = Input::get('access_token');
    $user_id = Input::get('user_id');
    
    $nickname = Input::get('nickname');
    $password = Input::get('password');
    $mobile = Input::get('mobile');
    $email = Input::get('email');
	$avatar = Input::get('avatar');
    //$input['invitecode'] = Input::get('invitecode');
    //$input['verifycode'] = Input::get('verifycode');
    $params = array('email'=>$email);
	if($avatar){
		$params['avatar'] = $avatar;
	}
    $result = Youxiduo\V4\User\UserService::loginByThird($type,$access_token,$user_id,$nickname,$password,$mobile,$params);
    if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));

Route::get('v4/user/verify_nickname',array('before'=>'uri_verify',function(){
	$nickname = Input::get('nickname');
	$uid = Input::get('uid',0);
	$result = Youxiduo\V4\User\UserService::isExistsByNickname($nickname,$uid);
	if($result===true){
		return OutUtility::outSuccess(array('available'=>true));
	}
	return OutUtility::outError(300,$result);
}));

Route::get('v4/user/send_phone_verifycode',array('before'=>'uri_verify',function(){
	$mobile = Input::get('mobile');
	$ip = Input::get('ip',null);
	$result = Youxiduo\V4\User\UserService::sendVerifyCodeByMobile($mobile,$ip);
	if($result===true){
		return OutUtility::outSuccess(array('success'=>true));
	}
	return OutUtility::outError(300,$result);
}));

Route::get('v4/user/check_phone_verifycode',array('before'=>'uri_verify',function(){
	$mobile = Input::get('mobile');
	$verifycode = Input::get('verifycode');
	$result = Youxiduo\V4\User\UserService::checkVerifyCodeByMobile($mobile, $verifycode);
	if($result===true){
		return OutUtility::outSuccess(array('available'=>true));
	}
	return OutUtility::outError(300,$result);
}));

Route::any('v4/user/create_phone_user',array('before'=>'uri_verify',function(){
	$mobile = Input::get('mobile');
	$password = Input::get('password');
	$verifycode = Input::get('verifycode');
	$params = array();
	$params['ip'] = Input::get('ip');
	$platform = Input::get('platform','android');
	$appname = Input::get('appname');
	$client = Input::get('client');
	if ($client) {
	    $c = $client;
	} elseif ($appname) {
	    $c = $appname;
	} else {
	    $c = 'android';
	}
	$idfa = Input::get('idfa','');
	$result = Youxiduo\V4\User\UserService::createUserByMobile($mobile, $password,$params,$platform,$c,$idfa);
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));

Route::get('v4/user/verify_mobile',array('before'=>'uri_verify',function(){
	$mobile = Input::get('mobile');
	$email = Input::get('email');
	$result = false;
	$result = Youxiduo\V4\User\UserService::isExistsByMobile($mobile);
	if($email){
		$result = Youxiduo\V4\User\UserService::isExistsByEmail($email);
	}
	if($result===true){
		return OutUtility::outSuccess(array('available'=>true));
	}
	return OutUtility::outError(300,$result);
}));

Route::any('v4/user/reset_password',array('before'=>'uri_verify',function(){
	$mobile = Input::get('mobile');
	$password = Input::get('password');
	$verifycode = Input::get('verifycode');
	$result = Youxiduo\V4\User\UserService::resetUserPassword($mobile,$verifycode,$password);
	if($result===true){
		return OutUtility::outSuccess(array('success'=>true));
	}
	return OutUtility::outError(300,$result);
}));

Route::any('v4/user/modify_password',array('before'=>'uri_verify',function(){
	$uid = Input::get('uid');
	$password = Input::get('password');
	$result = Youxiduo\V4\User\UserService::modifyUserPassword($uid, $password);
	if($result===true){
		return OutUtility::outSuccess(array('success'=>true));
	}
	return OutUtility::outError(300,$result);
}));

Route::any('v4/user/modify_mobile',array('before'=>'uri_verify',function(){
	$uid = Input::get('uid');
	$mobile = Input::get('mobile');
	$pwd = Input::get('password');
	$verifycode = Input::get('verifycode');
	$result = Youxiduo\V4\User\UserService::modifyUserMobile($uid,$mobile,$verifycode);
	if($result===true){
		return OutUtility::outSuccess(array('success'=>true));
	}
	return OutUtility::outError(300,$result);
}));

Route::any('v4/user/record_startup_info',array('before'=>'uri_verify',function(){
    $uid = Input::get('uid');//用户UID
    $apple_token = Input::get('apple_token','');//IOS推送令牌
    $idfa = Input::get('idfa','');//IOS设备标识
    $mac = Input::get('mac','');//IOS网卡信息
    $idcode = Input::get('idcode','');//安卓设备标识
    $os = Input::get('os','');//操作系统
    $osversion = Input::get('osversion','');//操作系统版本
    $ip = Input::get('ip','');//IP
    $longitude = Input::get('longitude','');//经度
    $latitude = Input::get('latitude','');//纬度
    $platform = Input::get('platform','');//平台
    
    $result = Youxiduo\V4\User\UserService::recordStartupInfo($uid,$apple_token,$idfa,$mac,$idcode,$os,$osversion,$ip,$longitude,$latitude,$platform);
    if($result===true){
		return OutUtility::outSuccess(array('success'=>true));
	}
}));
//附近的人
Route::any('v4/user/nearby_users',array('before'=>'uri_verify',function(){
    $uid = Input::get('uid');
    $longitude = Input::get('longitude','');//经度
    $latitude = Input::get('latitude','');//纬度
    $distance = Input::get('distance',500);//距离单位米
    $pageIndex = Input::get('pageIndex',1);
    $pageSize = Input::get('pageSize',10);
    $nickname = Input::get('nickname');
    $result = Youxiduo\V4\User\UserService::getNearbyUsers($uid, $longitude,$latitude,$distance,$pageIndex,$pageSize,$nickname);
    return OutUtility::outSuccess($result['result'],array('totalCount'=>$result['totalCount']));
}));

Route::any('v4/user/update_avatar',array('before'=>'uri_verify',function(){
	$uid = Input::get('uid',0);
	$avatar = Input::get('avatar');	
	$result = Youxiduo\V4\User\UserService::modifyUserAvatar($uid, $avatar);
	if($result===true){
		return OutUtility::outSuccess(array('success'=>true));
	}
	return OutUtility::outError(300,$result);
}));

Route::any('v4/user/modify_user',array('before'=>'uri_verify',function(){
    $uid = Input::get('uid',0);	
	$input = Input::only('nickname','email','sex','birthday','mobile','summary','avatar','alipay_num','alipay_name','homebg');
	$extra = Input::only('province','city','region');
	if($input['birthday']){
		$input['birthday'] = strtotime($input['birthday']);
	}
	$result = Youxiduo\V4\User\UserService::modifyUserInfo($uid, $input);
	Youxiduo\V4\User\UserService::updateUserArea($uid,$extra);
	if($result===true){
		return OutUtility::outSuccess(array('success'=>true));
	}
	return OutUtility::outError(300,$result);
}));

Route::get('v4/user/userinfo',array('before'=>'uri_verify',function(){
	$uid = Input::get('uid');
	$ouid = Input::get('ouid',0);
	$filter = Input::get('info','basic');
	if(strpos($uid,',')!==false){
		$uids = explode(',',$uid);
		$result = Youxiduo\V4\User\UserService::getMultiUserInfoByUids($uids,$filter,$ouid);
	}else{
	    $user = Youxiduo\V4\User\UserService::getUserInfoByUid((int)$uid,$filter,$ouid);
	    $result = is_array($user) ? array($user) : $user;
	}
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));

Route::get('v4/user/userinfolist',array('before'=>'uri_verify',function(){
	$uid = Input::get('uid');
	$ouid = Input::get('ouid',0);
	$filter = Input::get('info','basic');
	$appname = Input::get('appname','glwzry');
	if($uid){
	    if(strpos($uid,',')!==false){
	        $uids = explode(',',$uid);
	        $result = Youxiduo\V4\User\UserService::getMultiUserInfoByUids($uids,$filter,$ouid,$appname);
	    }else{
	        $user = Youxiduo\V4\User\UserService::getUserInfoByUid((int)$uid,$filter,$ouid,$appname);
	        $result = is_array($user) ? array($user) : $user;
	    }
	}else{
	    $pageIndex = (int)Input::get('pageIndex',1);
	    $pageSize = (int)Input::get('pageSize',10);
	    $result = Youxiduo\V4\User\UserService::getMultiUserInfolist($filter,$ouid,$pageIndex,$pageSize,true,$appname);
	}

	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));

Route::get('v4/user/is_newuser',array('before'=>'uri_verify',function(){
    $uid = Input::get('uid');
    $exists = Youxiduo\V4\User\UserService::isNewUser($uid);
    return OutUtility::outSuccess($exists ? true : false);
}));	
//用户所在区域
Route::get('v4/user/user_area',array('before'=>'uri_verify',function(){
    $uid = Input::get('uid');
    $result = Youxiduo\V4\User\UserService::getUserArea($uid);
    return OutUtility::outSuccess($result);
}));

Route::any('v4/user/add_attention',array('before'=>'uri_verify',function(){
	$uid = (int)Input::get('uid');
	$fuid = (int)Input::get('ouid');
    $result = Youxiduo\V4\User\RelationService::addAttention($uid, $fuid);
	if($result===true){
		return OutUtility::outSuccess(array('success'=>true));
	}
	return OutUtility::outError(300,$result);
}));

Route::any('v4/user/remove_attention',array('before'=>'uri_verify',function(){
	$uid = (int)Input::get('uid');
	$fuid = (int)Input::get('ouid');
    $result = Youxiduo\V4\User\RelationService::removeAttention($uid, $fuid);
	if($result===true){
		return OutUtility::outSuccess(array('success'=>true));
	}
	return OutUtility::outError(300,$result);
}));

Route::get('v4/user/attention',array('before'=>'uri_verify',function(){
	$uid = (int)Input::get('uid');
	$pageIndex = (int)Input::get('pageIndex',1);
	$pageSize = (int)Input::get('pageSize',10);
	$ouid = Input::get('ouid',0);
	$result = Youxiduo\V4\User\RelationService::getAttentionList($uid,$pageIndex,$pageSize);
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));

Route::get('v4/user/fans',array('before'=>'uri_verify',function(){
	$uid = (int)Input::get('uid');
	$pageIndex = (int)Input::get('pageIndex',1);
	$pageSize = (int)Input::get('pageSize',10);
	$result = Youxiduo\V4\User\RelationService::getFansList($uid,$pageIndex,$pageSize);
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));

Route::get('v4/user/friend',array('before'=>'uri_verify',function(){
	$uid = (int)Input::get('uid');
	$pageIndex = (int)Input::get('pageIndex',1);
	$pageSize = (int)Input::get('pageSize',10);
	$ouid = Input::get('ouid',0);
	$result = Youxiduo\V4\User\RelationService::getFriendList($uid,$pageIndex,$pageSize);
	if(is_array($result)){
		return OutUtility::outSuccess($result);
	}
	return OutUtility::outError(300,$result);
}));

//搜索用户
Route::get('v4/user/search_by_name',array('before'=>'uri_verify',function(){
	$username = Input::get('username');
	$pageIndex = (int)Input::get('pageIndex',1);
	$pageSize = (int)Input::get('pageSize',100);
	$ouid = Input::get('ouid',0);
	$result = Youxiduo\V4\User\UserService::searchByUserName($username,$pageIndex,$pageSize,$ouid);
// 	$total = Youxiduo\V4\User\UserService::searchCountByUserName($username);
	$total = 0;
	return OutUtility::outSuccess($result,array('totalCount'=>$total));
}));

//邀请用户
Route::get('v4/user/invite',array('before'=>'uri_verify',function(){
    $uid = Input::get('uid');
    $inviteCode = Input::get('invite_code');
    $result = Youxiduo\V4\User\UserService::inviteUser($uid, $inviteCode);
	if(is_bool($result)){
		return OutUtility::outSuccess(array('success'=>$result));
	}
	return OutUtility::outError(300,$result);
}));

//邀请记录
Route::get('v4/user/invite_list',array('before'=>'uri_verify',function(){
    $search['uid'] = Input::get('uid');
    $pageIndex = Input::get('pageIndex',1);
    $pageSize = Input::get('pageSize',10000);
    $result = Youxiduo\V4\User\UserService::inviteUserList($search,$pageIndex,$pageSize);
    return OutUtility::outSuccess($result['result'],array('totalCount'=>$result['totalCount']));
}));

//邀请数量
Route::get('v4/user/invite_count',array('before'=>'uri_verify',function(){
    $search['uid'] = Input::get('uid');
    $result = Youxiduo\V4\User\UserService::inviteUserCount($search);
    return OutUtility::outSuccess($result);
}));

Route::get('v4/user/invite_rank',array('before'=>'uri_verify',function(){
    $uid = Input::get('uid');
    $search['start_time'] = Input::get('start_time');
    $search['end_time'] = Input::get('end_time');
    $pageIndex = Input::get('pageIndex',1);
    $pageSize = Input::get('pageSize',50);
    $result = Youxiduo\V4\User\UserService::inviteRank($search,$pageIndex,$pageSize,$uid);
    return OutUtility::outSuccess($result);
}));

Route::get('v4/user/invite_top',array('before'=>'uri_verify',function(){
	$uid = Input::get('uid');
	$search['start_time'] = Input::get('start_time');
	$search['end_time'] = Input::get('end_time');
	$search['min'] = Input::get('min');
	$search['max'] = Input::get('max');
	$result = Youxiduo\V4\User\UserService::inviteTop($search,$uid);
	return OutUtility::outSuccess($result);
}));



//游币处理
Route::get('v4/user/do_money',array('before'=>'uri_verify',function(){
    $uid = Input::get('uid');
    $money = (int)Input::get('money');
    $experience = (int)Input::get('experience');
    $action = Input::get('action','');    
    $info = Input::get('info');
    $result = Youxiduo\V4\User\MoneyService::doCredit($uid, $money, $experience, $action, $info);
	if(is_bool($result)){
		return OutUtility::outSuccess(array('success'=>$result));
	}
	return OutUtility::outError(300,$result);
}));


/**
 * 匹配通讯录好友
 */
Route::any('v4/user/matching_friend',array('before'=>'uri_verify',function(){
    $uid = Input::get('uid');
    $mobile_str = strval(Input::get('mobile',''));
    $mobile_list = !empty($mobile_str) ? (strpos($mobile_str,',')!==false ? explode(',',$mobile_str):array($mobile_str)) : array();
    $result = Youxiduo\V4\User\UserService::matchingUserByMobile($mobile_list,$uid);
    return OutUtility::outSuccess($result);
}));

Route::get('user/reset_pwd',function(){
	
	return Illuminate\Support\Facades\View::make('reset-pwd');
});

Route::get('v4/user/user_tokens',array('before'=>'uri_verify',function(){
	$uids_str = Input::get('uid');
	$uids = !empty($uids_str) ? explode(',',$uids_str) : array();
	$pageIndex = Input::get('pageIndex',1);
	$pageSize = Input::get('pageSize',50);
	$result = Youxiduo\V4\User\UserService::getUserTokens($uids,$pageIndex,$pageSize);
	return OutUtility::outSuccess($result['result'],array('totalCount'=>$result['totalCount']));
}));


Route::post('user/reset_pwd',function(){

	$email = Input::get('email');
	$verifycode = Input::get('verifycode');
	$pwd = Input::get('pwd');
	$repwd = Input::get('repwd');
	//验证参数
	if(!$email) return Illuminate\Support\Facades\View::make('reset-pwd',array('error'=>'账号不能为空'));
	if(!$verifycode) return Illuminate\Support\Facades\View::make('reset-pwd',array('error'=>'验证码不能为空'));
	if(!$pwd) return Illuminate\Support\Facades\View::make('reset-pwd',array('error'=>'密码不能为空'));
	if(!$repwd) return Illuminate\Support\Facades\View::make('reset-pwd',array('error'=>'确认密码不能为空'));
	if($pwd != $repwd) return Illuminate\Support\Facades\View::make('reset-pwd',array('error'=>'两次输入的密码不一致'));
	//验证码
	$uid = Youxiduo\V4\User\Model\UserVerifyCode::checkEmailAndVerifyCode($email,$verifycode);
	if($uid){
		$res = Youxiduo\User\Model\Account::modifyUserPwd($uid,'uid',$pwd);
		if($res !== false){
			Youxiduo\V4\User\Model\UserVerifyCode::validVerifyCode($uid);
			$data = array('email'=>$email,'pwd'=>$pwd);
			return Illuminate\Support\Facades\View::make('reset-pwd-success',$data);
		}
	}
	return Illuminate\Support\Facades\View::make('reset-pwd',array('error'=>'账号或验证码错误，请重试'));
});

Route::get('v4/user/add_receipt_address',array('before'=>'uri_verify',function(){
    $data['consignee']  = Input::get('consignee', '');
    $data['phone']      = Input::get('phone', '');
    $data['zip_code']   = Input::get('zip_code', '');
    $data['region']     = Input::get('region', '');
    $data['address']    = Input::get('address', '');
    $data['is_default'] = Input::get('is_default', 0);
    $data['uid']        = Input::get('uid', 0);
    if ($data['is_default'] == 1) {
        Youxiduo\User\Model\AccountReceiptAddress::emptyDefaultAddress($data['uid']);
    }
    $result = Youxiduo\User\Model\AccountReceiptAddress::addReceiptAddress($data);

    if($result===true){
        return OutUtility::outSuccess(array('success'=>true));
    }
    return OutUtility::outError(300,$result);
}));

Route::get('v4/user/del_receipt_address',array('before'=>'uri_verify',function(){
    $addressId  = Input::get('addressId', '');
    $result = Youxiduo\User\Model\AccountReceiptAddress::delReceiptAddress($addressId);

    if($result===true){
        return OutUtility::outSuccess(array('success'=>true));
    }
    return OutUtility::outError(300,$result);
}));

Route::get('v4/user/update_receipt_address',array('before'=>'uri_verify',function(){
    $addressId  = Input::get('addressId');
    $data['consignee']  = Input::get('consignee', '');
    $data['phone']      = Input::get('phone', '');
    $data['zip_code']   = Input::get('zip_code', '');
    $data['region']     = Input::get('region', '');
    $data['address']    = Input::get('address', '');
    $uId                = Input::get('uid');
    $is_default         = Input::get('is_default', 0);
    $data = array_filter($data);
    if (!$addressId || !$uId) return OutUtility::outError(300, 'ERROR_PLATFORM_NOT_EXISTS');

    //is_default 唯一的处理
    if ($is_default == 1) {
        Youxiduo\User\Model\AccountReceiptAddress::updateDefaultAddress($addressId, $uId);
    }

    Youxiduo\User\Model\AccountReceiptAddress::updateReceiptAddress($addressId, $data);

    return OutUtility::outSuccess(array('success'=>true));

}));

Route::get('v4/user/search_receipt_address',array('before'=>'uri_verify',function(){
    $uId  = Input::get('uid');
    $default  = Input::get('default','all');
    if (!$uId) return OutUtility::outError(300, 'ERROR_PLATFORM_NOT_EXISTS');

    $result = Youxiduo\User\Model\AccountReceiptAddress::searchReceiptAddress($uId,$default);

    return OutUtility::outSuccess($result);
}));

Route::get('v4/user/update_default_address',array('before'=>'uri_verify',function(){
    $uId        = Input::get('uid', '');
    $addressId  = Input::get('addressId', '');
    if (!$addressId || !$uId) return OutUtility::outError(300, 'ERROR_PLATFORM_NOT_EXISTS');

    $result = Youxiduo\User\Model\AccountReceiptAddress::updateDefaultAddress($addressId, $uId);

    return OutUtility::outSuccess($result);
}));


Route::get('v5/user/demo',function(){
	//$result = Youxiduo\V5\User\UserService::createUserByMobile('18658170159','111111');
	//$result = Youxiduo\V5\User\UserHttpService::getUserInfo(100240,'device|info');
	//$result = Youxiduo\V5\User\UserService::updateNickname(5748099,'我是大玩家');
	//$result = Youxiduo\V5\User\UserService::sendMobileVerifyCode('18658179152');
	//$result = Youxiduo\V5\User\UserService::checkMobileVerifyCode('18658179152','1234');
	//$result = Youxiduo\V5\User\UserService::login('18658179152','111111w',array());
	//$result = Youxiduo\V5\User\UserHttpService::searchResult(array('like_nickname'=>'三国%'),1,10,array('uid'=>'desc'),true);
	//$result = Youxiduo\V5\User\UserHttpService::matchUserByUserId(array(100240,100135,100));
	//var_dump($result);
	//return $result;
});