<?php
namespace modules\v4system\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Youxiduo\V4\User\MoneyService;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\Utility;
use modules\v4system\models\Config as v4Config;

class ConfigController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'v4system';
    }

    public function getSetting()
    {
        $data = array();
        $data['setting'] = v4Config::getSetting();
        return $this->display('app-setting',$data);
    }

    public function getShareSetting()
    {
        $data = array();
        $data['setting'] = v4Config::getSetting();
        return $this->display('app-share-setting',$data);
    }

    public function getMoneySetting()
    {
        $data = array();
        $data['setting'] = v4Config::getSetting();
        return $this->display('money-setting',$data);
    }

    public function postMoneySetting()
    {
        $newHandAward = Input::get('newHandAward',0);
        $updateUserAward = Input::get('updateUserAward',0);
        $result = v4Config::saveMoneySetting($newHandAward,$updateUserAward);
        if($result){
            return $this->redirect('v4system/config/money-setting');
        }
        return $this->back('保存失败');
    }

    public function postSetting()
    {
        $aboutUsUrl = Input::get('aboutUsUrl');
        $aboutUsImg = Input::get('aboutUsImg');
        $aboutUsNewHand = Input::get('aboutUsNewHand');
        $userAgreement = Input::get('userAgreement');

        $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
        $path = storage_path() . $dir;
        //列表图
        if(Input::hasFile('filedata')){

            $file = Input::file('filedata');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $aboutUsImg = $dir . $new_filename . '.' . $mime;
            $aboutUsImg = Utility::getImageUrl($aboutUsImg);
        }

        $result = v4Config::saveAppSetting($aboutUsImg,$aboutUsUrl,$aboutUsNewHand,$userAgreement);

        if($result){
            return $this->back('保存成功');
        }else{
            return $this->back('保存失败');
        }
    }

    public function postShareSetting()
    {
        $shareTitle = Input::get('shareTitle');
        $shareContent = Input::get('shareContent');
        $shareImg = Input::get('shareImg');
        $shareUrl = Input::get('shareUrl');
        $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
        $path = storage_path() . $dir;
        //列表图
        if(Input::hasFile('filedata')){

            $file = Input::file('filedata');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $shareImg = $dir . $new_filename . '.' . $mime;
            $shareImg = Utility::getImageUrl($shareImg);
        }
        $result = v4Config::saveShareSetting($shareTitle,$shareContent,$shareImg,$shareUrl);
        if($result){
            return $this->back('保存成功');
        }else{
            return $this->back('保存失败');
        }
    }

    public function getSignMoney()
    {
        $data = array();
        $data['setting'] = v4Config::getSign();
        return $this->display('sign-money',$data);
    }

    public function postSignMoney()
    {
        $initPrize = Input::get('initPrize');
        $addValue  = Input::get('addValue');
        $maxValue  = Input::get('maxValue');

        $result = v4Config::saveUpdateSign($initPrize,$addValue,$maxValue);
        if($result){
            return $this->back('保存成功');
        }else{
            return $this->back('保存失败');
        }
    }
    
    //攻略设置
    
    public function getGLSetting()
    {
        $data = array();
        $data['setting'] = v4Config::getSetting('1.0','glwzry');
        return $this->display('gl-setting',$data);
    }
    
    public function getGLShareSetting()
    {
        $data = array();
        $data['setting'] = v4Config::getSetting('1.0','glwzry');
        return $this->display('gl-share-setting',$data);
    }
    
    public function postGLSetting()
    {
        $aboutUsUrl = Input::get('aboutUsUrl');
        $aboutUsImg = Input::get('aboutUsImg');
        $aboutUsNewHand = Input::get('aboutUsNewHand');
        $userAgreement = Input::get('userAgreement');
    
        $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
        $path = storage_path() . $dir;
        //列表图
        if(Input::hasFile('filedata')){
    
            $file = Input::file('filedata');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $aboutUsImg = $dir . $new_filename . '.' . $mime;
            $aboutUsImg = Utility::getImageUrl($aboutUsImg);
        }
    
        $result = v4Config::saveAppSetting($aboutUsImg,$aboutUsUrl,$aboutUsNewHand,$userAgreement,'1.0','glwzry');
    
        if($result){
            return $this->back('保存成功');
        }else{
            return $this->back('保存失败');
        }
    }
    
    public function postGLShareSetting()
    {
        $shareTitle = Input::get('shareTitle');
        $shareContent = Input::get('shareContent');
        $shareImg = Input::get('shareImg');
        $shareUrl = Input::get('shareUrl');
        $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
        $path = storage_path() . $dir;
        //列表图
        if(Input::hasFile('filedata')){
    
            $file = Input::file('filedata');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $shareImg = $dir . $new_filename . '.' . $mime;
            $shareImg = Utility::getImageUrl($shareImg);
        }
        $result = v4Config::saveShareSetting($shareTitle,$shareContent,$shareImg,$shareUrl,'1.0','glwzry');
        if($result){
            return $this->back('保存成功');
        }else{
            return $this->back('保存失败');
        }
    }
    
}