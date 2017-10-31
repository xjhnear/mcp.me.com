<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/3/3
 * Time: 11:53
 */
namespace Youxiduo\V4\Game; 

use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;


class SchemeService extends BaseService{
    const API_URL_CONF = 'app.28888_api_url';

    public static function searchRecordList($params){
        $params_ = array('gid','schemeKey','id','active','pageIndex','pageSize','platform');
    
        $tmp_result = Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'module_game/match/get_game_match_list_backend');
        return array('errorCode'=>$tmp_result['errorCode'],'totalCount'=>!empty($tmp_result['totalCount'])?$tmp_result['totalCount']:0,'result'=>!empty($tmp_result['result'])?$tmp_result['result']:array());
    }

    //编辑发放计划
    public static function updateRecord($params)
    {   $params_=array('gid','schemeKey','id','active');
    return Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'module_game/match/update_game_match');
    }
    
}
