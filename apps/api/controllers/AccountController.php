<?php
use Yxd\Modules\Message\NoticeService;
use Yxd\Services\PassportService;
use Yxd\Services\UserService;
use LucaDegasperi\OAuth2Server\Proxies\AuthorizationServerProxy;
use LucaDegasperi\OAuth2Server\Facades\AuthorizationServerFacade as AuthorizationServer;
use League\OAuth2\Server\Exception\ClientException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Mews\Captcha\CaptchaCache;


class AccountController extends BaseController 
{			
	/**
	 * 游戏多用户登录
	 */
	public function getLogin()
	{
		$time = (int)microtime(true);
		$params = array(
			'client_id'=>'youxiduo',
			'redirect_uri'=>'localhost',
			'timestamp'=>$time,
			'client_secret'=>'90909090'
		);
		$signature = md5(http_build_query($params));
		$client_id = 'youxiduo';
		$redirect_uri = 'localhost';
		$timestamp = $time;
		$account_type = 'youxiduo'; 
		$email = Input::get('email'); 
		$password = Input::get('pwd');
		$third_access_token = Input::get('third_access_token','');
		$third_token = array();
		if(Input::get('from') && Input::get('snsuid')){
			$third_token['type'] = strtolower(Input::get('from'));
			$third_token['type_uid'] = Input::get('snsuid');
			$third_token['access_token'] = Input::get('snstoken');
			$third_token['expires_in'] = Input::get('expire');
		}
		
	    if(!$email){
			return $this->fail(11211,'Email不能为空');
		}
	
	    if(!$password){
			return $this->fail(11211,'密码不能为空');
		}
 		$result = PassportService::checkAuthorize($client_id, $redirect_uri, $timestamp, $signature, $account_type, $email, $password, $third_access_token);
 		//此处需补全H5注册的缺少的参数
		$input = Input::only('idfa','mac','openudid','osversion');
		//$apple_token = Input::get('token');
		$input['apple_token'] = Input::get('token');
		if($result['status']===200){
			$uid = $result['data']['uid'];
			$result = array('result'=>array('uid'=>$result['data']['uid']));
			UserService::updateUserExtend($uid,$input);
			//绑定第三方
			if($third_token){
				PassportService::bindThird($result['data']['uid'],$third_token);
			}
			return $this->success($result);
		}else{
			return $this->fail(1121,$result['error_description']);
		}
	}
		
	
	/**
	 * 注册
	 */
	public function getRegister()
	{
		$time = (int)microtime(true);
		$params = array(
			'client_id'=>'youxiduo',
			'redirect_uri'=>'localhost',
			'timestamp'=>$time,
			'client_secret'=>'90909090'
		);
		$signature = md5(http_build_query($params));
		$client_id = 'youxiduo';
		$redirect_uri = 'localhost';
		$timestamp = $time;
		$account_type = 'youxiduo';
		$user['email'] = Input::get('email'); 
		$user['password'] = Input::get('pwd');
		$user['nickname'] = Input::get('nickname','');
		$user['avatar'] = Input::get('avatar','');
		$user['vuser'] = Input::get('vuser',0);
		//推荐人的id号
		if(Input::has('tuijian_id'))
		{
			$tuijian_id = Input::get('tuijian_id');
		}

		$third_token = array();
	    if(!$user['email']){
			return $this->fail(11211,'Email不能为空');
		}
		
	    if(!$user['password']){
			return $this->fail(11211,'密码不能为空');
		}
		if(Config::get('app.verifycode',false)===true){
			$hashcode = Input::get('hashtoken');
			$value = Input::get('verifycode');
			$captcha = CaptchaCache::instance();		
			if($captcha::check($hashcode,$value)===false){
				//return $this->fail(11211,'验证码错误');
			}
		}		
		$result = PassportService::createUser($client_id, $redirect_uri, $timestamp, $signature, $user,$third_token);
		$input = Input::only('idfa','mac','openudid','osversion');
		$input['apple_token'] = Input::get('token');
	    if($result['status']===200){
	    	UserService::updateUserExtend($result['data']['uid'],$input);
	    	
		    if(isset($tuijian_id) && !empty($tuijian_id))
			{
				$res = UserService::checkOnlyMobile($input['idfa'], $input['mac']);
				if($res){
				    UserService::getCheckUserById($tuijian_id, $result['data']['uid'], $input['idfa'], $input['mac']);
				}else{
					$args = array('zhucema'=>$tuijian_id);
					NoticeService::sendExistsIOS($result['data']['uid'],$args);
				}					
			}			
			return $this->success(array('result'=>array('uid'=>$result['data']['uid'])));
		}else{
			return $this->fail(11211,$result['error_description']);
		}
	}
	
	/**
	 * 验证邮箱是否可用
	 */
	public function getVerifyEmail()
	{
		$email = Input::get('email');
		$result = PassportService::checkEmailIsExists($email);
		if($result['status'] === 200){
			return $this->success(array('result'=>''));
		}else{
			return $this->fail(11211,$result['error_description']);
		}
	}
	
	public function getVerifyCode()
	{
		$hashcode = Input::get('hashtoken');		
		$captcha = CaptchaCache::instance();
		return $captcha::create($hashcode);
		//$img_str = file_get_contents('http://user.youxiduo.com/regtoken/' . $hashcode);
		//header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
        //header('Pragma: no-cache');
        //header("Content-type: image/jpg");
        //$img = imagecreatefromjpeg('http://user.youxiduo.com/regtoken/' . $hashcode);
        //$img = imagecreatefromstring($img_str);
        //return Response::stream(function()use($img){return imagejpeg($img);},200,array('Content-Type'=>'image/png'));
	}	
	
	public function getCheckVerifyCode()
	{
	    $hashcode = Input::get('hashcode');
		$value = Input::get('verifycode');
		$captcha = CaptchaCache::instance();		
		if($captcha::check($hashcode,$value)===false){
			return $this->fail(11211,'验证码错误');
		}else{
			return $this->success(array('result'=>''));
		}
	}
	
	
	/**
	 * 第三方登录
	 */
	public function getSnslogin()
	{
		$snsuid = Input::get('snsuid');
		$snstoken = Input::get('snstoken');
		$from = strtolower(Input::get('from'));
		$third_token['type'] = $from;
		$third_token['type_uid'] = $snsuid;
	    $input = Input::only('idfa','mac','openudid','osversion');
	    $apple_token = Input::get('token');
		//自动绑定第三方帐号
		if(isset($input['idfa']) && !empty($input['idfa'])){
			if($third_token['type']=='sina'){
				$third_type=1;
			}elseif($third_token['type']=='qq'){
				$third_type=2;				
			}
			if(isset($third_type)){
				$third_uid = $third_token['type_uid'];
				$uid = PassportService::verifyAppUser($input['idfa'],'idfa',$third_type,$third_uid);				
				if($uid!==false && $uid>0){
					$account = UserService::getUserInfo($uid,'full');
					if($account){
					    return $this->success(array('result'=>array('ishas'=>1,'uid'=>$uid,'email'=>$account['email'])));
					}
				}
			}
		}elseif(isset($input['mac']) && !empty($input['mac']) && $input['mac']!=='02:00:00:00:00:00'){
		    if($third_token['type']=='sina'){
				$third_type=1;
			}elseif($third_token['type']=='qq'){
				$third_type=2;				
			}
			if(isset($third_type)){
				$third_uid = $third_token['type_uid'];
				$uid = PassportService::verifyAppUser($input['mac'],'mac',$third_type,$third_uid);				
				if($uid!==false && $uid>0){
					$account = UserService::getUserInfo($uid,'full');
					if($account){
					    return $this->success(array('result'=>array('ishas'=>1,'uid'=>$uid,'email'=>$account['email'])));
					}
				}
			}
		}else{
			if($third_token['type']=='sina'){
				$third_type=1;
			}elseif($third_token['type']=='qq'){
				$third_type=2;				
			}
			if(isset($third_type)){
				$third_uid = $third_token['type_uid'];
				$uid = PassportService::verifyWebUser($third_type, $third_uid);
			    if($uid!==false && $uid>0){
					$account = UserService::getUserInfo($uid,'full');
					if($account){
					    return $this->success(array('result'=>array('ishas'=>1,'uid'=>$uid,'email'=>$account['email'])));
					}
				}
			}
		}
		
		return $this->success(array('result'=>array('ishas'=>0,'uid'=>0,'email'=>'')));
	    $time = (int)microtime(true);
		$params = array(
			'client_id'=>'youxiduo',
			'redirect_uri'=>'localhost',
			'timestamp'=>$time,
			'client_secret'=>'90909090'
		);
		
		$signature = md5(http_build_query($params));
		
		$client_id = 'youxiduo';
		$redirect_uri = 'localhost';
		$timestamp = $time;
		$account_type = $from; 
		$email = null; 
		$password = null;
		$third_access_token = $snstoken;
	    if(!$third_access_token){
			return $this->fail(11211,'Token不能为空');
		}
			    
		$result = PassportService::checkAuthorize($client_id, $redirect_uri, $timestamp, $signature, $account_type, $email, $password, $third_access_token);
		
		if($result['status']===200){
			UserService::updateUserExtend($result['data']['uid'],array('apple_token'=>$apple_token));	
			$result = array('result'=>array('ishas'=>0,'uid'=>$result['data']['uid']));			
			return $this->success($result);
		}elseif($result['status']===1203){
			$result = array('result'=>array('ishas'=>0,'uid'=>0));
			return $this->success($result);
		}else{
			return $this->fail(11211,$result['error_description']);
		}
	}
	
	/**
	 * 第三方注册
	 */
	public function getSnsregister()
	{
	    $time = (int)microtime(true);
		$params = array(
			'client_id'=>'youxiduo',
			'redirect_uri'=>'localhost',
			'timestamp'=>$time,
			'client_secret'=>'90909090'
		);
		
		$signature = md5(http_build_query($params));
		
		$client_id = 'youxiduo';
		$redirect_uri = 'localhost';
		$timestamp = $time;
		$account_type = 'youxiduo'; 
		
		$user['email'] = Input::get('email'); 
		$user['password'] = Input::get('pwd');
		$user['nickname'] = Input::get('nick','');
		$user['sex'] = Input::get('gender',0);
		$user['avatar'] = Input::get('avatar','');
		$third_token = array();
		$third_token['type'] = strtolower(Input::get('from'));
		$third_token['type_uid'] = Input::get('snsuid');
		$third_token['access_token'] = Input::get('snstoken');
		$third_token['expires_in'] = Input::get('expire');
		//推荐人的id号
		if(Input::has('tuijian_id'))
		{
			$tuijian_id = Input::get('tuijian_id');
		}
		$input = Input::only('idfa','mac','openudid','osversion');
		$input['apple_token'] = Input::get('token');
		//自动绑定第三方帐号
		if(isset($input['idfa']) && !empty($input['idfa'])){
			if($third_token['type']=='sina'){
				$third_type=1;
			}elseif($third_token['type']=='qq'){
				$third_type=2;				
			}
			if(isset($third_type)){
				$third_uid = $third_token['type_uid'];
				$uid = PassportService::verifyAppUser($input['idfa'],'idfa',$third_type,$third_uid);				
				if($uid!==false && $uid>0){
					$account = UserService::getUserInfo($uid,'full');
					if($account){
					    return $this->success(array('result'=>array('ishas'=>1,'uid'=>$uid,'email'=>$account['email'])));
					}
				}
			}
		}elseif(isset($input['mac']) && !empty($input['mac']) && $input['mac']!=='02:00:00:00:00:00'){
		    if($third_token['type']=='sina'){
				$third_type=1;
			}elseif($third_token['type']=='qq'){
				$third_type=2;				
			}
			if(isset($third_type)){
				$third_uid = $third_token['type_uid'];
				$uid = PassportService::verifyAppUser($input['mac'],'mac',$third_type,$third_uid);				
				if($uid!==false && $uid>0){
					$account = UserService::getUserInfo($uid,'full');
					if($account){
					    return $this->success(array('result'=>array('ishas'=>1,'uid'=>$uid,'email'=>$account['email'])));
					}
				}
			}
		}
		
	    if(!$user['email']){
			return $this->fail(11211,'Email不能为空');
		}
		
	    if(!$user['password']){
			return $this->fail(11211,'密码不能为空');
		}
		
		
		$result = PassportService::createUser($client_id, $redirect_uri, $timestamp, $signature, $user,$third_token);
	    				
	    if($result['status']===200){	 
	    	UserService::updateUserExtend($result['data']['uid'],$input); 
		    if(isset($tuijian_id) && !empty($tuijian_id))
			{
			    $res = UserService::checkOnlyMobile($input['idfa'], $input['mac']);
				if($res){
				    UserService::getCheckUserById($tuijian_id, $result['data']['uid'], $input['idfa'], $input['mac']);
				}else{
					$args = array('zhucema'=>$tuijian_id);
					NoticeService::sendExistsIOS($result['data']['uid'],$args);
				}	
			}	
			return $this->success(array('result'=>array('uid'=>$result['data']['uid'])));
		}else{
			return $this->fail(11211,$result['error_description']);
		}
	}		
	
	/**
	 * 注销
	 */
	public function postLogout()
	{
		$access_token = Input::get('access_token');
		PassportService::doLogout($access_token);
		return $this->send(200,null);
	}
	
	public function bindSns()
	{				
		$email = Input::get('email'); 
		$password = Input::get('pwd');
		$third_token['type'] = strtolower(Input::get('from'));
		$third_token['type_uid'] = Input::get('snsuid');
		$third_token['access_token'] = Input::get('snstoken');
		$third_token['expires_in'] = Input::get('expire');
		$third_token['refresh_token'] = '';
		$input = Input::only('idfa','mac','openudid','osversion','token');
	    
	    $user = PassportService::bindThirdAccount($email,$password,$third_token);
	    if($user===null){
	    	return $this->fail(11211,'账号不存在');
	    }elseif($user===-1){
	    	return $this->fail(11211,'密码错误');
	    }elseif(is_array($user)){
	    	UserService::updateUserExtend($user['uid'],$input);//更新附加参数	    	
	    	return $this->success(array('result'=>array('ishas'=>1,'uid'=>$user['uid'],'email'=>$email)));
	    }else{
	    	return $this->fail(11211,'登录失败');
	    }
	}
	
}
