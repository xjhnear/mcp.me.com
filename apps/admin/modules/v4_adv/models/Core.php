<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/15
 * Time: 11:27
 */

namespace modules\v4_adv\models;

use Illuminate\Support\Facades\Config;

class Core extends BaseHttp
{
    const HOST_URL = 'app.18888_api_url';

    public static function delcache($data)
    {
        $apiUrl = Config::get(self::HOST_URL) . 'iosv4-service-control/cache/del_key';
        $params = $data;
        $result = self::http($apiUrl,$params,'GET','json');
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }
    
}