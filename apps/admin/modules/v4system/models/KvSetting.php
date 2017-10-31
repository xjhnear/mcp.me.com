<?php
namespace modules\v4system\models;

use Illuminate\Support\Facades\Config as baseConfig;

class KvSetting extends BaseHttp
{
    const HOST_URL = 'app.18888_api_url';
    public static function queryConfigList($pageIndex=1,$pageSize=100,$isLoadCount=true)
    {
        $apiUrl =  baseConfig::get(self::HOST_URL) . 'iosv4-service-control/config/query_config_list';
        $params = array(
            'pageIndex'=>$pageIndex,
            'pageSize'=>$pageSize,
            'isLoadCount'=> $isLoadCount ? 'true':'false'
        );

        $result = self::http($apiUrl,$params,'GET');
        if($result['errorCode']==0){
            return $result['result'];
        }
        return array();
    }

    public static function getConfigDetail($id)
    {
        $apiUrl =  baseConfig::get(self::HOST_URL) . 'iosv4-service-control/config/get_config_detail';

        $params = array();
        $params['incrementalId'] = $id;

        $result = self::http($apiUrl,$params,'GET');
        if($result['errorCode']==0){
            return $result['result'];
        }
        return array();
    }

    public static function updateConfig($id,$configType,$configValue,$configDesc)
    {
        $apiUrl =  baseConfig::get(self::HOST_URL) . 'iosv4-service-control/config/update_config';

        $params = array();
        $params['incrementalId'] = intval($id);
        $params['configType'] = $configType;
        $params['configValue'] = $configValue;
        $params['configDesc'] = $configDesc;

        $result = self::http($apiUrl,$params,'GET','json');
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }
    
    public static function insertConfig($id,$configType,$configValue,$configDesc)
    {
        $apiUrl =  baseConfig::get(self::HOST_URL) . 'iosv4-service-control/config/save_config';
    
        $params = array();
        $params['incrementalId'] = intval($id);
        $params['configType'] = $configType;
        $params['configValue'] = $configValue;
        $params['configDesc'] = $configDesc;

        $result = self::http($apiUrl,$params,'GET','json');
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }
    
}