<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/15
 * Time: 11:27
 */

namespace modules\v4_adv\models;

use Illuminate\Support\Facades\Config;

class BtnDownload extends BaseHttp
{
    const HOST_URL = 'app.28888_api_url';
    public static function getList($pageIndex=1,$pageSize=10)
    {
        $params = array('pageIndex'=>$pageIndex,'pageSize'=>$pageSize);
        $apiUrl = Config::get(self::HOST_URL) . 'module_game/game_download/get_game_download_list';

        $result = self::http($apiUrl,$params);
        if($result['errorCode']==0 && $result['result']) {
            return array('result'=>$result['result'],'totalCount'=>$result['totalCount']);
        }
    }

    public static function getListNew($params)
    {
        $apiUrl = Config::get(self::HOST_URL) . 'module_game/game_download/get_game_download_list';
        $result = self::http($apiUrl,$params);
//        echo($apiUrl);
//        print_r($params);
//        print_r($result);
        if($result['errorCode']==0 && $result['result']) {
            return array('result'=>$result['result'],'totalCount'=>$result['totalCount']);
        }
    }

    public static function getInfo($id)
    {
        $apiUrl = Config::get(self::HOST_URL) . 'module_game/game_download/get_game_download_list';

        $params['id'] = $id;
        $params['isThirdShow'] = 1;
        $result = self::http($apiUrl,$params);
        if($result['errorCode']==0 && $result['result']) {
            isset($result['result'][0]['linkValue']) && $result['result'][0]['linkValue'] = json_decode($result['result'][0]['linkValue'],true);
            return $result['result'][0];
        }
        return array();
    }

    public static function save($data)
    {
        $apiUrl = Config::get(self::HOST_URL) . 'module_game/game_download/save_update_game_download';
        $params = $data;
        $result = self::http($apiUrl,$params,'POST','json');
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }
    

    public static function del($data)
    {
        $apiUrl = Config::get(self::HOST_URL) . 'module_game/game_download/del_thirdVendors';
        $params = $data;
        $result = self::http($apiUrl,$params,'POST','json');
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }
    
    public static function delgamedown($data)
    {
        $apiUrl = Config::get(self::HOST_URL) . 'module_game/game_download/del_game_download';
        $params = $data;
        $result = self::http($apiUrl,$params,'POST','json');
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }
    
}