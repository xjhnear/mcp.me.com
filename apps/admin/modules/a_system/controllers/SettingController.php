<?php
namespace modules\a_system\controllers;

use Yxd\Modules\System\SettingService;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;
use libraries\Helpers;
use Youxiduo\System\Model\AppConfig;



class SettingController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'a_system';
	}
	
	public function getIndex()
	{
		return $this->getWebSetting();
	}
	
	public function getWebSetting()
	{
		$data = array();
		$config = SettingService::getConfig('android_setting');
		if($config){
			$data['web'] = $config['data'];
		}
		return $this->display('web-setting',$data);
	}
	
	public function postSaveWeb()
	{
		$input = Input::only('name','meta_description','meta_keywords','icp','filter_words');
		$input['close_comment'] = Input::get('close_comment',0);
		$input['close_topic'] = Input::get('close_topic',0);
		SettingService::setConfig('android_setting', $input);
		return $this->redirect('a_system/setting/index');
	}
	
	public function getAppVersionList()
	{
		$data = array();
		$search = array('platform'=>'android');
		$data['datalist'] = AppConfig::m_search($search);
		return $this->display('app-version-list',$data);
	}
	
	public function getAppVersion($id=0)
	{		
		$data = array();
		if($id){
			$data['version'] = AppConfig::m_getVersionInfo($id);
		}else{
			$data['version'] = array('versionstate'=>2,'scorestate'=>2);
		}
		return $this->display('app-version-info',$data);
	}
	
	public function postSaveAppVersion()
	{
		$input = Input::only('id','showname','appname','version','channel','versionstate','scorestate');
		
		$validator = Validator::make($input,array(
		    'version'=>'required',		    
		));
		if($validator->fails()){
			if($validator->messages()->has('version')){
				return $this->back()->with('global_tips','版本号不能为空');
			}
		}
		$append = Input::only('lm','ss','dl','adv','updateversion','updateword','isforce','lt','gg','bar','apkurl');
		
		$input['append'] = json_encode($append);
        if(!$input['id']){
        	$input['betaopen'] = '123lizxliyuhuifs';
        	$input['rateopen'] = '123youxiduocwanfs';
        }
        $input['isshow'] = 1;
        $input['platform'] = 'android';
		AppConfig::m_saveVersionInfo($input);
		
		return $this->redirect('a_system/setting/app-version-list')->with('global_tips','版本配置保存成功');
	}
	
	/**
	 * 清除所有模板缓存
	 */
	public function getClean(){
		$tmp_zht_dir = storage_path().'/cache';
		Helpers::delTree($tmp_zht_dir,$tmp_zht_dir);
		echo 1;
	}
}