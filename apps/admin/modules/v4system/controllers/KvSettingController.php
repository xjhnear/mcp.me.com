<?php
namespace modules\v4system\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Config;
use libraries\Helpers;
use modules\v4system\models\KvSetting;

class KvSettingController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'v4system';
    }

    public function getList()
    {
        $data = array();
        $data['datalist'] = KvSetting::queryConfigList(1,100);
        return $this->display('kv-setting-list',$data);
    }

    public function getEdit($id=null)
    {
        $data = array();
        if($id){
            $data['setting'] = KvSetting::getConfigDetail($id);
        }

        return $this->display('kv-setting-info',$data);
    }

    public function postEdit()
    {
        $input = Input::all();
        $id = Input::get('incrementalId');
        $configType = Input::get('configType');
        $configValue = Input::get('configValue');
        $configDesc = Input::get('configDesc');
        $configId = Input::get('configId');
        
        if(!empty($input['pic'])){
            $dir = '/userdirs/common/icon_v4/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['pic']);
            $configValue = Config::get('app.img_url') . $path;
        }

        if ($id) {
            $result = KvSetting::updateConfig($id,$configType,$configValue,$configDesc);
        } else {
            $result = KvSetting::insertConfig($configId,$configType,$configValue,$configDesc);
        }

        if($result){
            return $this->redirect('v4system/kvsetting/list','保存成功');
        }else{
            return $this->back('保存失败');
        }
    }
}