<?php
namespace Yxd\Services;

use Yxd\Models\User;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Yxd\Models\Passport;
use Yxd\Services\Models\AccountThirdLogin;
use Yxd\Services\Models\Account;

class PassportService extends Service
{
	
	public static function getZhucema()
	{
	    return User::getZhucema();
	}
	
	public static function getUpdateAccount()
	{
		$zhucema = self::getZhucema();
		$data=array('zhucema' => $zhucema);
		return $data;
	}
	
	/**
	 * 登录
	 */
	protected static function doLogin($account_type,$email,$password,$access_token)
	{
		
		if($account_type == 'youxiduo'){
			if(!$email || !$password){
				$param = $email ? 'password' : 'email';
				return self::send(1101,null,'invalid_request','请求参数[' . $param . ']丢失,请检查请求参数是否完整');
			}
			//本地账号登陆
			return self::localLogin($email, $password);
		}else{
		    if(!$access_token){
		    	$param = 'third_access_token';
				return self::send(1101,null,'invalid_request','请求参数[' . $param . ']丢失,请检查请求参数是否完整');
			}
			//第三方账号登陆
			return self::thirdLogin($account_type, $access_token);
		}
	}	
	
	/**
	 * 本地帐号登录
	 */
	protected static function localLogin($identify,$password,$identify_type='email')
	{
		$user = Passport::verifyLocalLogin($identify, $password,$identify_type);
		if($user === null){
			return self::send(1201,null,'invalid_account','无效的登录帐号');
		}elseif($user === -1){
			return self::send(1202,null,'invalid_password','登录密码错误');
		}else{
			//Event::fire('user.login',array(array($user)));
			return self::send(200,UserService::filterUserFields($user));
		}
	}
	
	/**
	 * 第三方帐号登录
	 */
	protected static function thirdLogin($account_type,$access_token)
	{
		$user = Passport::verifyThirdLogin($account_type, $access_token);
		if($user === null){
			return self::send(1203,null,'invalid_third_access_token','第三方登录帐号尚未绑定');
		}else{
			Event::fire('user.login',array(array($user,array('accout_type'=>$account_type,'access_token'=>$access_token))));
			return self::send(200,UserService::filterUserFields($user));
		}
	}
	
	public static function verifyAppUser($identify,$identify_type='idfa',$third_type,$third_uid)
	{
		return self::verifyWebUser($third_type, $third_uid);
		if(!in_array($identify_type,array('idfa','mac'))) return false;
		$uids = Account::db()->where($identify_type,'=',$identify)->lists('uid');
		$insert = true;
		if(!$uids){
			$third = AccountThirdLogin::db()->where('type','=',$third_type)->where('type_uid','=',$third_uid)->first();
			if($third) return $third['uid'];
		}else{
			$thirds = AccountThirdLogin::db()->whereIn('uid',$uids)->where('type','=',$third_type)->get();
			if(!$thirds){
				$data = array('uid'=>$uids[0],'type'=>$third_type,'type_uid'=>$third_uid);
				AccountThirdLogin::db()->insertGetId($data);
				return $uids[0];
			}
			
			foreach($thirds as $row){
				if($third_type==$row['type'] && $third_uid == $row['type_uid']){
					return $row['uid'];
				}elseif($third_type==$row['type']){
					$insert = false;
				}
			}
		}
		if($insert===true){
			$data = array('uid'=>$uids[0],'type'=>$third_type,'type_uid'=>$third_uid);
			AccountThirdLogin::db()->insertGetId($data);
			return $uids[0];
		}
		return false;
	}
	
	public static function verifyWebUser($third_type,$third_uid)
	{
		$third = AccountThirdLogin::db()->where('type','=',$third_type)->where('type_uid','=',$third_uid)->first();
		if(!$third){
			return false;
		}
		return $third['uid'];
	}
	
	public static function bindThird($uid,$third_token)
	{
		if(!$third_token['from'] || !$third_token['snsuid'] || !$third_token['access_token']) return false;
		Passport::bindThirdLogin($uid, $third_token['from'], $third_token['snsuid'], $third_token['access_token'], $third_token['expires_in'],'');
		return true;
	} 
	
	public static function bindThirdAccount($email,$password,$third_token)
	{
		$user = Passport::verifyLocalLogin($email,$password,'email');
	    if($user === null){
			return null;
		}elseif($user === -1){
			return -1;
		}else{
			$uid = $user['uid'];
			$type = $third_token['type'];
			$type_uid = $third_token['type_uid'];
			$access_token = $third_token['access_token'];
			$expires_in = $third_token['expires_in'];
			$refresh_token = $third_token['refresh_token'];
			
			Passport::bindThirdLogin($uid, $type, $type_uid, $access_token, $expires_in, $refresh_token);
			return UserService::filterUserFields($user);
		}
	}
	
	/**
	 * 退出登录
	 */
	public static function doLogout($access_token)
	{
		$server = self::authServer();
		$token = $server->getStorage('access_token')->getAccessToken($access_token);
		if($token){			
			$server->getStorage('access_token')->expireAccessToken($access_token);
			Event::fire('user.logout',array($token));
		}
		
		
		return true;
	}
	
	/**
	 * 访问令牌转换为UID
	 */
	public static function accessTokenToUid($access_token)
	{
		$server = self::authServer();
		$token = $server->getStorage('access_token')->getAccessToken($access_token);
		if($token){
			return $token;
		}
		return false;
	}
	
	/**
	 * 签名验证
	 */
	public static function verifySignture($client_id,$redirect_uri,$timestamp,$signature,$client_secret)
	{
		$signture_str = 'client_id='.$client_id.'&redirect_uri='.$redirect_uri.'&timestamp='.$timestamp.'&client_secret=' . $client_secret;
		if(md5($signture_str) != $signature){
			return false;
		}
		return true;
	}
	
	/**
	 * 登录验证
	 */
	public static function checkAuthorize($client_id,$redirect_uri,$timestamp,$signature,$account_type,$email,$password,$access_token)
	{		
		/*	
		$auth_params = array(
		    'client_id'=>$client_id,
		    'response_type'=>'code',
		    'state'=>'default'
		);
		$auth_request = new \OAuth2\Request($auth_params);
		
		$auth_response = new \OAuth2\Response();
		
		$server = self::authServer();
	
		if(!$server->validateAuthorizeRequest($auth_request)){
			//return $auth_response->send(); 
			return self::send(1101,null,'invalid_request','请求参数丢失,请检查请求参数是否完整');
		}
		
		$client = $server->getStorage('client')->getClientDetails($client_id);
		
		if(!self::verifySignture($client_id, $redirect_uri, $timestamp, $signature, $client['client_secret'])){
			return self::send(1105,$client,'invalid_signture','无效的签名');
		}
		*/
		$result = self::doLogin($account_type, $email, $password, $access_token);
			
		if($result['status'] !==200){
			return $result;
		}
		$user = $result['data'];
		/*
		$response = new \OAuth2\Response();
        $server->handleAuthorizeRequest($auth_request,$response,true,$user['uid']);
		$code = substr($response->getHttpHeader('Location'), strpos($response->getHttpHeader('Location'), 'code=')+5, 40);
		
		
		$token_params = array_merge($client,array(
		    'code'=>$code,
		    'grant_type' => 'authorization_code'
		));
		$token_request = new \OAuth2\Request(array(),$token_params);
		$token_request->server['REQUEST_METHOD'] = 'POST';
		$token_response = new \OAuth2\Response();
		$token = $server->grantAccessToken($token_request,$token_response);
		if(!$token){
			return self::send($token_response->getStatusCode(),null,$token_response->getParameter('error'),$token_response->getParameter('error_description'));
		}
		*/
		$token['uid'] = $user['uid'];
		$token['user'] = $user;
		return self::send(200,$token);
	}
	
	/**
	 * 验证邮箱是否被占用
	 * @param string $email
	 * @return number 1212/200
	 */
	public static function checkEmailIsExists($email)
	{
	    if(Passport::checkEmailIsExists($email)){
			return self::send(1212,null,'email_exists','Email已经被占用');
		}
		return self::send(200,null);
	}
	
	/**
	 * 验证昵称是否被占用
	 * @param string $nickname
	 * @return number 1212/200
	 */
	public static function checkNickNameIsExists($nickname)
	{
		if(Passport::checkEmailIsExists($nickname)){
			return self::send(1212,null,'nickname_exists','昵称已经被占用');
		}
		return self::send(200,null);
	}
	
	/**
	 * 创建新帐号
	 * @param string $client_id
	 * @param string $redirect_uri
	 * @param string $timestamp
	 * @param string $signature
	 * @param array  $user
	 * @param array  $third_token
	 * 
	 * @return array 
	 */
	public static function createUser($client_id,$redirect_uri,$timestamp,$signature,$user,$third_token=array())
	{
		/*
	    $auth_params = array(
		    'client_id'=>$client_id,
		    'response_type'=>'code',
		    'state'=>'default'
		);
		$auth_request = new \OAuth2\Request($auth_params);
			
		$server = self::authServer();
		if(!$server->validateAuthorizeRequest($auth_request)){
			return self::send(1101,null,'invalid_request','请求参数丢失,请检查请求参数是否完整');
		}

		$client = $server->getStorage('client')->getClientDetails($client_id);
		
		if(!self::verifySignture($client_id, $redirect_uri, $timestamp, $signature, $client['client_secret'])){
			return self::send(1105,$client,'invalid_signture','无效的签名');
		}
		*/
		//
		if(Passport::checkEmailIsExists($user['email'])){
			return self::send(1212,$user,'email_exists','Email已经被占用');
		}
		
		if(isset($user['nickname']) && !empty($user['nickname']) && Passport::checkNickNameIsExists($user['nickname']))	{
			return self::send(1213,null,'nickname_exists','昵称已经被占用');
		}
		$user = \Yxd\Models\User::createAccount($user);
		if(!$user){
			return self::send(1105,null,'server_error','服务器端错误');
		}
		//用户注册事件
		Event::fire('user.register',array(array($user)));
		Event::fire('user.update_userinfo_cache',array(array($user['uid'])));
		if($third_token){
			$bind = true;
			foreach(array('type','type_uid','access_token','expires_in') as $key){
				if(!isset($third_token[$key]) || !$third_token[$key]){
					$bind = false;
				}
			}
			if($bind==true){
			    Passport::bindThirdLogin($user['uid'], $third_token['type'], $third_token['type_uid'], $third_token['access_token'], $third_token['expires_in'],0);
			}
		}
		/*
		$response = new \OAuth2\Response();
        $server->handleAuthorizeRequest($auth_request,$response,true,$user['uid']);
		$code = substr($response->getHttpHeader('Location'), strpos($response->getHttpHeader('Location'), 'code=')+5, 40);
		
		$token_params = array_merge($client,array(
		    'code'=>$code,
		    'grant_type' => 'authorization_code'
		));
		$token_request = new \OAuth2\Request(array(),$token_params);
		$token_request->server['REQUEST_METHOD'] = 'POST';
		$token_response = new \OAuth2\Response();
		$token = $server->grantAccessToken($token_request,$token_response);
		if(!$token){
			return self::send($token_response->getStatusCode(),null,$token_response->getParameter('error'),$token_response->getParameter('error_description'));
		}
		*/
		$token['uid'] = $user['uid'];
		$token['user'] = UserService::filterUserFields($user);
		return self::send(200,$token);
	}		
	
	/**
	 * 返回认证服务器
	 */
	protected static function authServer()
	{
		$config = array(
		    'require_exact_redirect_uri'=>false,
		    'access_lifetime'=>3600*24*7
		);
		$conf = Config::get('app.oauth2',array('driver'=>'redis'));
		if($conf['driver']=='redis'){
			$redis = \Illuminate\Support\Facades\Redis::connection();
			$storage = new \OAuth2\Storage\Redis($redis);
		}else{
			$tables = array(
	            'client_table' => 'yxd_oauth2_clients',
	            'access_token_table' => 'yxd_oauth2_access_tokens',
	            'refresh_token_table' => 'yxd_oauth2_refresh_tokens',
	            'code_table' => 'yxd_oauth2_authorization_codes',
	            'user_table' => 'yxd_oauth2_users',
	            'jwt_table'  => 'yxd_oauth2_jwt',
	            'scope_table'  => 'yxd_oauth2_scopes',
	            'public_key_table'  => 'yxd_oauth2_public_keys',
	        );
			$storage = new \OAuth2\Storage\Pdo($conf['pdo'],$tables);
	}
				
		$server = new \OAuth2\Server($storage,$config);
		return $server;
	}
}