<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/14
 * Time: 13:55
 */
namespace modules\v4message\models;

use Illuminate\Support\Facades\Config;

class Tpl extends BaseHttp
{
    const HOST_URL = 'app.28888_api_url';
    public static function getList()
    {
        $params = array('pageIndex'=>1,'pageSize'=>100,'platform'=>'ios');
        $apiUrl = Config::get(self::HOST_URL) . 'module_message/sys_mess_template/get_sys_mess_template_list';

        $result = self::http($apiUrl,$params);
        if($result['errorCode']==0 && $result['result']) {
            return $result['result'];
        }
    }

    public static function getInfo($id)
    {
        $apiUrl = Config::get(self::HOST_URL) . 'module_message/sys_mess_template/get_sys_mess_template_list';

        $params['id'] = $id;
        $result = self::http($apiUrl,$params);
        if($result['errorCode']==0 && $result['result']) {
            return $result['result'][0];
        }
        return array();
    }

    public static function save($data)
    {
        $apiUrl = Config::get(self::HOST_URL) . 'module_message/sys_mess_template/save_sys_mess_template';
        $params = $data;
        $params['platform'] = 'ios';
        $result = self::http($apiUrl,$params,'POST','json');
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }
}