<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/3/3
 * Time: 11:53
 */
namespace Youxiduo\Monitor;

use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;


class RecordService extends BaseService{
    const API_URL_CONF = 'app.monitor_api_url';

    public static function searchRecordList($params){
        $params_ = array('baseId','addtime','pageIndex','pageSize','urlName');
    
        $tmp_result = Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'url/list');
        return array('errorCode'=>$tmp_result['errorCode'],'totalCount'=>!empty($tmp_result['totalCount'])?$tmp_result['totalCount']:0,'result'=>!empty($tmp_result['result'])?$tmp_result['result']:array());
    }

    public static function searchProcessList($params){
        $params_ = array('baseId','addtime','pageIndex','pageSize','processName');
    
        $tmp_result = Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'service/list');
        return array('errorCode'=>$tmp_result['errorCode'],'totalCount'=>!empty($tmp_result['totalCount'])?$tmp_result['totalCount']:0,'result'=>!empty($tmp_result['result'])?$tmp_result['result']:array());
    }
    
    public static function Delete($params)
    {   $params_=array('addtime');
    return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'url/del');
    }
    
}
