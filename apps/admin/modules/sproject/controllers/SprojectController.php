<?php
namespace modules\sproject\controllers;

use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Paginator;
//use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\System\SettingService;



class SprojectController extends BackendController {
	public function _initialize() {
		$this->current_module = 'sproject';
	}

	/**
	 * 修改显示界面
	 * @param int $gid
	 */
	public function getEdit() {
		$data = array();
		$config = SettingService::getConfig('sproject_attestation');
		if($config){
			$data['result'] = $config['data'];
		}
		return $this->display('sproject-edit',$data);
	
	}
	
	/**
	 * 保存
	 */
	public function postSave() {
		$parameter['title'] = Input::get('title');
		$parameter['info'] = Input::get('info');
		$parameter['detail'] = Input::get('detail');
		$dir = '/userdirs/sproject/' . date('Y') . '/' . date('m') . '/';
		$path = storage_path() . $dir;
		if(Input::hasFile('icon')){
			$file = Input::file('icon');
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();
			$file->move($path,$new_filename . '.' . $mime );
			$parameter['icon'] = $dir . $new_filename . '.' . $mime;
		}
		
		//验证规则
		$validator['title'] = $validator['info'] = $validator['detail'] = 'required';
		$validator['icon'] = 'image';
		
		//错误信息返回
		$errmessage['required'] = '不能为空';
		$errmessage['image'] = '缩略图格式不正确';
		//验证
		$validator = Validator::make($parameter, $validator, $errmessage);
		if ($validator->fails()) {
			return Redirect::back()->withErrors($validator)->withInput();
		}
		$parameter['icon'] = empty($parameter['icon']) ? Input::get('oldicon') : $parameter['icon'];
		SettingService::setConfig('sproject_attestation', $parameter);
		return $this->redirect('sproject/attestation/edit','修改完成');
	}

}