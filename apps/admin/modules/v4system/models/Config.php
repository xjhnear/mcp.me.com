<?php
namespace modules\v4system\models;

use Illuminate\Support\Facades\Config as baseConfig;

class Config extends BaseHttp
{
    public static function getSetting($version='4.0',$appname='yxdjqb',$platform='ios')
    {
        $apiurl = baseConfig::get(self::HOST_URL) . 'module_adapter_other/share_about/get_share_about_list';
        $params = array(
            'version'=>$version,
            'appname'=>$appname,
            'platform'=>$platform
        );
        $result = self::http($apiurl,$params,'GET');
        if($result['errorCode']==0){
            if (isset($result['result'][0])) {
                return $result['result'][0];
            }
        }
        return array();

    }

    public static function saveShareSetting($shareTitle,$shareContent,$shareImg,$shareUrl,$version='4.0',$appname='yxdjqb',$platform='ios')
    {
        $apiurl = baseConfig::get(self::HOST_URL) . 'module_adapter_other/share_about/save_share_about';
        $params = array(
            'shareTitle'=>$shareTitle,
            'shareContent'=>$shareContent,
            'shareImg'=>$shareImg,
            'shareUrl'=>$shareUrl,
            'version'=>'4.0',
            'version'=>$version,
            'appname'=>$appname,
            'platform'=>$platform
        );

        $result = self::http($apiurl,$params,'POST','json');
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }

    public static function saveAppSetting($aboutUsImg,$aboutUsUrl,$aboutUsNewHand,$userAgreement,$version='4.0',$appname='yxdjqb',$platform='ios')
    {
        $apiurl = baseConfig::get(self::HOST_URL) . 'module_adapter_other/share_about/save_share_about';
        $params = array(
            'aboutUsImg'=>$aboutUsImg,
            'aboutUsUrl'=>$aboutUsUrl,
            'aboutUsNewHand'=>$aboutUsNewHand,
            'userAgreement'=>$userAgreement,
            'version'=>'4.0',
            'version'=>$version,
            'appname'=>$appname,
            'platform'=>$platform
        );
        $result = self::http($apiurl,$params,'POST','json');
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }

    public static function saveMoneySetting($newHandAward,$updateUserAward,$version='4.0',$appname='yxdjqb',$platform='ios')
    {
        $apiurl = baseConfig::get(self::HOST_URL) . 'module_adapter_other/share_about/save_share_about';
        $params = array(
            'newHandAward'=>$newHandAward,
            'updateUserAward'=>$updateUserAward,
            'version'=>'4.0',
            'version'=>$version,
            'appname'=>$appname,
            'platform'=>$platform
        );
        $result = self::http($apiurl,$params,'POST','json');
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }

    public static function getSign()
    {
        $apiUrl = baseConfig::get(self::HOST_URL) . 'module_activity/sign/get_sign_template';
        $params = array();
        $result = self::http($apiUrl,$params,'GET');
        if($result['errorCode']==0){
            return $result['result'];
        }
        return array();
    }

    public static function saveUpdateSign($initPrize,$addValue,$maxValue)
    {
        $apiUrl = baseConfig::get(self::HOST_URL) . 'module_activity/sign/save_update_sign';
        $params = array(
            'initPrize'=>$initPrize,
            'addValue'=>$addValue,
            'maxValue'=>$maxValue
        );
        $result = self::http($apiUrl,$params,'POST','json');
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }
}