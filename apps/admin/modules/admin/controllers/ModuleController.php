<?php
namespace admin\controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

use Yxd\Modules\Core\BackendController;

use Youxiduo\System\Model\Module;

class ModuleController extends BackendController
{	
	public function _initialize(){
		$this->current_module = 'admin';
	}
	
    public function getList()
	{
		$data = array();
		$data['datalist'] = Module::getList(1,50);
		return $this->display('module_list',$data);
	}
	
	public function getInfo($id)
	{
		$data = array();
		$data['module'] = Module::getInfo($id);
		return $this->display('module_install',$data);
	}
	
	public function postFind()
	{
		$module_name = Input::get('module_name');
		$config_file = app_path() . '/modules/' . $module_name . '/config.php';
		if(is_file($config_file) && is_readable($config_file)){
			$module = require $config_file;
			$data['module'] = $module;
			return $this->display('module_install',$data);
		}else{
			return $this->back()->with('global_tips',$this->lang('system.module_not_exists'));
		}		
	}
	
	/**
	 * 安装新模块
	 */
	public function getInstall()
	{
		$data = array();
		return $this->display('module_install',$data);
	}
	
	/**
	 * 保存模块
	 */
	public function postInstall()
	{
		
		$input = Input::only('module_name','module_type','module_alias','module_desc');
		$input['sort'] = Input::get('sort',9);
		if ($input['sort'] == '') $input['sort'] = 9;
		//验证
		$rules = array(
		    'module_name'=>'required',
		    'module_type'=>'required',
		    'module_alias'=>'required',
		    'module_desc'=>'required'
		);
		$validator = Validator::make($input,$rules);
		if($validator->fails()){
			$messages = $validator->messages()->all();
			$errors = '';
			foreach($messages as $message){
				$errors .= $message . '<br/>';
			}
			return $this->redirect('admin/module/install',$errors);
		}

		$result = Module::saveInfo($input);
		if($result){
			return $this->redirect('admin/module/list',$this->lang('system.module_install_success'));
		}else{
			return $this->back($this->lang('system.module_install_fail'));
		}
	}
	
	public function getUninstall($module_id)
	{
		return $this->back('该功能还在建设中,敬请期待');
	}
}