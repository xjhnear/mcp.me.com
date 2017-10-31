<?php
namespace modules\system\controllers;

use modules\system\models\VersionModel;

use Yxd\Modules\Core\CacheService;

use Yxd\Modules\System\SettingService;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;
use libraries\Helpers;
use Youxiduo\Helper\MyHelp;
use Youxiduo\System\Model\AppConfig;



class SettingController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'system';
	}
	
	public function getIndex()
	{
		return $this->getWebSetting();
	}
	
	public function getWebSetting()
	{
		$data = array();
		$config = SettingService::getConfig('pcweb_setting');
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
		SettingService::setConfig('pcweb_setting', $input);
		return $this->redirect('system/setting/index');
	}
	
	public function getAppVersionList()
	{
		$data = array();
		$search = array('platform'=>'ios');
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
		$input = Input::only('id','showname','appname','version','channel','appstoreurl','versionstate','scorestate','intro','syspic-count','sys_img');
		
		$validator = Validator::make($input,array(
		    'version'=>'required',		    
		));
		if($validator->fails()){
			if($validator->messages()->has('version')){
				return $this->back()->with('global_tips','版本号不能为空');
			}
		}
		$append = Input::only('lm','ss','dl','adv','updateversion','updateword','isforce','lt','gg','bar');
		
		$input['append'] = json_encode($append);
        if(!$input['id']){
        	$input['betaopen'] = '123lizxliyuhuifs';
        	$input['rateopen'] = '123youxiduocwanfs';
        }
        $input['channel'] = '';
        $input['isshow'] = 1;
        $input['platform'] = 'ios';
        
        $sys_img_arr = $input['sys_img']?explode(',',$input['sys_img']):array();
        $syspic_count = explode(',', $input['syspic-count']);
        $sys_img_arr_new = array();
        foreach ($syspic_count as $row) {
            if(Input::hasFile('sys_img_'.$row)){
                $sys_img_arr_new[] = MyHelp::save_img_no_url(Input::file('sys_img_'.$row),'home');
            }else{
                if (isset($sys_img_arr[$row])) {
                    $sys_img_arr_new[] = $sys_img_arr[$row];
                }
                unset($input['sys_img_'.$row]);
            }
        }
        $input['sys_img'] = implode(',',$sys_img_arr_new);
        unset($input['syspic-count']);
		AppConfig::m_saveVersionInfo($input);
		
		return $this->redirect('system/setting/app-version-list')->with('global_tips','版本配置保存成功');
	}
	
	/**
	 * 清除所有模板缓存
	 */
	public function getClean(){
		$tmp_zht_dir = storage_path().'/cache';
		Helpers::delTree($tmp_zht_dir,$tmp_zht_dir);
		echo 1;
	}

    //修改执行时间
    public function getAjaxUpdateVersion(){
        if(Input::get('id')){
            $input = Input::get();

            $key_arr = array('lm','ss','dl','adv','updateversion','updateword','isforce','lt','gg','bar');
            $lt = $input['lt'];
            unset($input['lt']);
//             $version = AppConfig::m_getVersionInfo(Input::get('id'));
            $search['platform'] = 'ios';
            $version = AppConfig::m_search($search);
            $res = false;
            if($version){
                foreach ($version as $info) {
                    $append = json_decode($info['append'],true);
                    is_array($append) && $info = array_merge($info,$append);
                    $info['syspicVendorsList'] = explode(',', $info['sys_img']);
                    foreach ($info['syspicVendorsList'] as &$item) {
                        $item = MyHelp::getImageUrl($item);
                    }
                    if (isset($info['syspicVendorsList'])) {
                        $info['syspiccount'] = implode(',',array_keys($info['syspicVendorsList']));
                    } else {
                        $info['syspiccount'] = '';
                    }
                    
                    $append = array();
                    foreach($info as $k=>&$v){
                        if(in_array($k,$key_arr)){
                            $append[$k] = $v;
                            unset($info[$k]);
                        }
                    }
                    $append['lt'] = $lt;
                    $input['append'] = json_encode($append);
                    $input['id'] = $info['id'];
                    if (AppConfig::m_saveVersionInfo($input)) {
                        $res = true;
                    }
                }

            } else {
                echo json_encode(array('success'=>"false",'mess'=>'无数据','data'=>""));
            }

            if($res){
                echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>""));
            }else{
                echo json_encode(array('success'=>"false",'mess'=>'修改失败','data'=>""));
            }
        }else{
            echo json_encode(array('success'=>"false",'mess'=>'无id值','data'=>""));
        }

    }
}