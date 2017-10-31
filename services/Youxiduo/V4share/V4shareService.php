<?php
namespace Youxiduo\V4share;

use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Config;
use Youxiduo\Helper\BaseHttp;

class V4shareService extends BaseService{

    const API_URL_CONF = "app.V4share_api_url";
    const API_URL_28888 = "app.28888_api_url";

    public static $arr = array(
        'AddShareConfig' => array("share/v4/AddShareConfig",'POST'),//新增分享配置
        'UpdateShareConfig' => array("share/v4/UpdateShareConfig",'POST'),//修改分享配置
        'GetShareConfigList' => array("share/v4/GetShareConfigList",'POST'),//查询分享配置列表
        'GetShareConfigDetail' => array("share/v4/GetShareConfigDetail",'POST'),//查询分享配置详情
        'EnableShareConfig' => array("share/v4/EnableShareConfig",'POST'),//开启分享配置
        'DisableShareConfig' => array("share/v4/DisableShareConfig",'POST'),//关闭分享配置
        'GetShareRecordList' => array("share/v4/GetShareRecordList",'POST'),//分享记录查询
        'GetShareRewardList' => array("share/v4/GetShareRewardList",'POST'),//分享奖励统计查询
        'GetShareRanking' => array("share/v4/GetShareRanking",'POST'),//分享人数排行
        'GetShare' => array("share/v4/GetShare",'POST'),//开始分享接口（获取分享地址）
        'ChooseReward' => array("share/v4/ChooseReward",'POST'),//上线选择奖励
        'ChooseBait' => array("share/v4/ChooseBait",'POST'),//下线选择奖励
        'NewUserComing' => array("share/v4/NewUserComing",'POST'),//分享下线注册完成
        'GetLowerInfoList' => array("share/v4/GetLowerInfoList",'POST'),//分享下线查询
        'add_update_share_config' => array("ios_module_share/add_update_share_config",'POST'),//ios自分享新增编辑
        'get_share_config_detail' => array("ios_module_share/get_share_config_detail",'GET'),//ios自分享详情查询
        'get_share_config_list' => array("ios_module_share/get_share_config_list",'GET'),//ios自分享列表查询
        'get_record_list' => array("ios_module_share/get_record_list",'GET'),//ios后台分享记录
        'getShareLowerRecordList' => array("share/v4/getShareLowerRecordList",'POST'),//获取新用户集合
        'get_new_uid_list' => array("ios_module_share/get_new_uid_list",'GET'),//获取新用户集合
    );

    //完成
    public static function excute($data=array(),$method="",$isList=true)
    {
        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF).self::$arr[$method][0],$data,self::$arr[$method][1]);
        if(!$res)
            return array('success'=>false,'error'=>"接口不存在或参数错误",'data'=>false);
        if(!$res['errorCode']) {
            if($isList){
                if(isset($res['result']))
                    return array('success' => true, 'error' => false, 'data' => $res['result'],'count' => isset($res['totalCount'])?$res['totalCount']:0);
            }else{
                return array('success' => true, 'error' => false, 'data' => false);
            }
        }
        return array('success'=>false,'error'=>$res,'data'=>false);
    }
    public static function excute2($data=array(),$method="",$isList=true)
    {
        $res = BaseHttp::http(Config::get(self::API_URL_CONF).self::$arr[$method][0],$data,self::$arr[$method][1]);
//         echo Config::get(self::API_URL_CONF).self::$arr[$method][0];
//         print_r($data);
//         print_r($res);
        if(!$res){
            return array('success'=>false,'error'=>"接口不存在或参数错误",'data'=>false);
        }
        if(!$res['errorCode']) {
            if($isList){
                if(isset($res['result'])&&$res['result'])
                    return array('success' => true, 'error' => false, 'data' => $res['result']);
            }else{
                return array('success' => true, 'error' => false, 'data' => false);
            }
        }else{
            return array('success'=>false,'error'=>$res['errorDesc'],'data'=>false);
        }
    }

    public static function excute3($data=array(),$method="",$isList=true)
    {
        $res = BaseHttp::http(Config::get(self::API_URL_28888).self::$arr[$method][0],$data,self::$arr[$method][1],'json');
        if(!$res){
            return array('success'=>false,'error'=>"接口不存在或参数错误",'data'=>false);
        }
        if(!$res['errorCode']) {
            if($isList){
                if(isset($res['result'])&&$res['result']){
                    return array('success' => true, 'error' => false, 'data' => $res['result'],'totalCount' => isset($res['totalCount'])?$res['totalCount']:0);
                }else{
                    return array('success' => true, 'error' => false, 'data' => $res['result'],'totalCount' => isset($res['totalCount'])?$res['totalCount']:0);
                }
            }else{
                return array('success' => true, 'error' => false, 'data' => false);
            }
        }else{
            return array('success'=>false,'error'=>$res['errorDesc'],'data'=>false);
        }
    }


  }