<?php
namespace modules\zt_activity\controllers;

use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Paginator;
//use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\System\SettingService;



class HopeController extends BackendController {
	public function _initialize() {
		$this->current_module = 'zt_activity';
	}

	/**
	 * 修改显示界面
	 * @param int $gid
	 */
	public function getEdit() {
		$data = array();
		$config = SettingService::getConfig('hope_list');

		if($config){
			$data['result'] = $config['data'];
		}
		return $this->display('hope-edit',$data);
	}
	
	/**
	 * 保存
	 */
	public function postSave() {
		$parameter['ids'] = $ids = Input::get('ids');
		//验证规则
		$validator['ids'] = 'required';
		//错误信息返回
		$errmessage['required'] = '不能为空';
		//验证
		$validator = Validator::make($parameter, $validator, $errmessage);
		if ($validator->fails()) {
			return $this->back()->withErrors($validator)->withInput();
		}
        $ids = str_replace(" ", "", $ids);
        $ids = str_replace("，", ",", $ids);
        $ids = str_replace(",,", ",", $ids);
        $ids = explode(',',$ids);
        $ids = array_unique($ids);
        foreach($ids as $v){
            if(!is_numeric($v)) return $this->back($v.'不是数字');
        }
        $c = count($ids);
        if( $c != 10){
            return $this->back("个数不对或者有重复ID，现在只有:$c");
        }
        $parameter['ids'] = implode(',',$ids);
		SettingService::setConfig('hope_list', $parameter);
		return $this->redirect('zt_activity/hope/edit','修改完成');
	}

}