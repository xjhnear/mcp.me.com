<?php
namespace modules\common\controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;

use Yxd\Modules\Core\BackendController;
use Youxiduo\System\AuthService;
use Youxiduo\System\Model\Admin;
use Youxiduo\System\Model\AuthGroup;

class HomeController extends BackendController
{
    public function _initialize()
	{
		$this->current_module = 'common';
	}
	
	public function getIndex()
	{
		return $this->display('profilehome');
	}
	
	public function getEditProfile()
	{
		$admin_id = $this->current_user['id'];
		$data = array();
		$data['admininfo'] = Admin::getInfoById($admin_id);
		$groups = AuthGroup::getNameList();		
		$data['groups'] = $groups;
		$data['is_super'] = AuthService::verifyNodeAuth('admin/admin/modify-pwd');
		return $this->display('profile',$data);
	}
	
	public function postEditProfile()
	{
	    $input = Input::only('username','authorname','realname','password');
	    $input['id'] = $this->current_user['id'];
		if($input['id']){
			unset($input['username']);
		}
		if(empty($input['password'])){
			unset($input['password']);
		}
		if(isset($input['username'])&&$input['username']){
			$exists = Admin::getInfoByUsername($input['username']);
			if($exists){
				return $this->back('账号名已经存在');
			}
		}

		$result = Admin::saveInfo($input);
		if($result){
			return $this->redirect('common/home/edit-profile','账号保存成功');
		}else{
			return $this->back('账号保存成功');
		}
	}
}