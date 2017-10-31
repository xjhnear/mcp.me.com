<?php

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\App;
use Illuminate\Routing\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

use Youxiduo\System\AuthService;

class LoginController extends Controller
{
	public function __construct(){
		$lang = Session::get('language_lang');
		if($lang == 'zht'){
			App::setLocale('zht');
		}else{
			App::setLocale('zhs');
		}
	}
	
	/**
	 * 设置网站语言
	 * @param string $lang
	 */
	public function getSetLanguage(){
		$lang = Input::get('lang');
		if($lang == 'zht'){
			Session::put('language_lang','zht');
		}else{
			Session::put('language_lang','zhs');
		}
	}

	public function getIndex()
	{
		$data = array();
		$data['error'] = Session::get('global_tips');
		return View::make('login',$data);
	}
	
	public function postDo()
	{
		$email = Input::get('email');
		$pwd   = Input::get('pwd');
		$remember = Input::get('remember',false) ? true : false;
		$admin = AuthService::doLogin($email,$pwd,$remember);
		if($admin===false){
			return $this->redirect('login','账号或密码错误');
		}elseif(is_array($admin)){
			if(isset($admin['cookie'])){
				return $this->redirect('common/home/index','登录成功')->withCookie($admin['cookie']);
			}						
			return $this->redirect('common/home/index','登录成功');
		}
		return $this->redirect('login','账号或密码错误');		
	}
	
	public function getLogout()
	{
		$cookie = AuthService::doLogout();
		return $this->redirect('login')->withCookie($cookie);
	}
	
    /**
	 * 跳转
	 * @param string $route 路由URI
	 * @param string $tips 提示信息
	 */
	protected function redirect($route,$tips='')
	{
		if($tips){
			return Redirect::to($route)->with('global_tips',$tips);
		}
		return Redirect::to($route);
	}
}