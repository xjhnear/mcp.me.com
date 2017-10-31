<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/15
 * Time: 11:27
 */

namespace modules\v4_my\models;

use Illuminate\Support\Facades\Config;

class Image extends BaseHttp
{
    const HOST_URL = 'app.28888_api_url';
    public static function getList($pageIndex=1,$pageSize=10,$uid='')
    {
        $params = array('pageIndex'=>$pageIndex,'pageSize'=>$pageSize,'uid'=>$uid,'isShowCount'=>1);
        $apiUrl = Config::get(self::HOST_URL) . 'module_adapter_other/relevance/get_game_file_list';

        $result = self::http($apiUrl,$params);
        if($result['errorCode']==0 && $result['result']) {
            return array('result'=>$result['result'],'totalCount'=>$result['totalCount']);
        }
    }

    public static function del($id)
    {
        $apiUrl = Config::get(self::HOST_URL) . 'module_adapter_other/relevance/del_game_file';
        $params = array('id'=>$id,'isActive'=>false);
        $result = self::http($apiUrl,$params,'POST','json');
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }
}