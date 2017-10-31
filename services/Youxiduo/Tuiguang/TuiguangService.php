<?php
namespace Youxiduo\Tuiguang;

use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Config;
use modules\yxvl_eSports\models\BaseHttp;

class TuiguangService extends BaseService{

    const API_URL_CONF="app.Tuiguang_api_url";
    const V4_API_URL_CONF="app.48080_api_url";
    const V4GAME_API_URL_CONF="app.7069_api_url";
    const CHAN_API_URL_CONF="app.11080_api_url";
    public static $arr = array(
        'cashlist' => array("module_rmb/account/cashlist",'GET'),//提现列表
        'cashapprove' => array("module_rmb/account/cashapprove",'POST'),//提现审批

        'promoteruser' => array("module_promoter/promoteruser/list",'GET'),//推广用户列表
        'promoteruser/ios' => array("module_promoter_ios/promoteruser/list",'GET'),//ios推广用户列表
        'promoter' => array("module_promoter/promoter/list",'GET'),//推广员列表
        'promoter/ios' => array("module_promoter_ios/promoter/list",'GET'),//ios推广员列表
        'promoterGame' =>array("module_promoter/promotion/promoterGame",'GET'),//推广员游戏分成查询
        'promoterGameAdd'=>array("module_promoter/promotion/promoterGameAdd",'POST'),
        'money' => array("module_promoter/stance/list",'GET'),//现金分成流水
        'money/ios' => array("module_promoter_ios/stance/list",'GET'),//ios用户充值
        'yb' => array("/module_promoter/stance/list",'GET'),//游币分成流水
        'account/statistics' =>array("/module_rmb/account/statistics",'GET'),//账户APP收入查询
        'account/exportUid' =>array("/module_rmb//account/exportUid",'GET'),//导出用户文件
        
        'ReturnGameList4Page'   =>array("ios/ReturnGameList4Page",'GET'),//根据游戏name查询游戏的列表
        'returnChannelsByGameId' =>array("ios/ReturnChannelsByGameId",'GET'),//根据游戏id查询渠道
        
        'queryGame'   =>array("module_promoter/promotion/queryGame",'GET'),//添加游戏分成
        'queryGame/ios'   =>array("module_promoter_ios/promotion/queryGame",'GET'),//ios添加游戏分成
        'addGame'   =>array("module_promoter/promotion/addGame",'POST'),//添加游戏分成
        'addGame/ios'   =>array("module_promoter_ios/promotion/addGame",'POST'),//ios添加游戏分成
        'updateGame'   =>array("module_promoter/promotion/updateGame",'POST'),//更改游戏分成
        'updateGame/ios'   =>array("module_promoter_ios/promotion/updateGame",'POST'),//更改游戏分成
        
        'GetAdminAppList'   =>array("Manage/GetAdminAppList",'POST'),//sdk游戏查询
        'GetAgentInfoList' =>array("Manage/GetAgentInfoList",'POST'),//android sdk 渠道
        
        'update_promoter' => array("module_promoter/promotion/update_promoter",'POST'),//
        'update_promoter/ios' => array("module_promoter_ios/promotion/update_promoter",'POST'),//ios

        'default/list' => array("module_promoter/default/list",'GET'),//
        'default/update' => array("module_promoter/default/update",'POST'),//修改分成设置
        'default/update/ios' => array("module_promoter_ios/default/update",'POST'),//修改分成设置
        'config/list' => array("module_promoter/config/list",'GET'),//规则
        'config/update' => array("module_promoter/config/update",'GET'),//规则
        'config/update/ios' => array("module_promoter_ios/config/update",'GET'),//ios规则
        
        'account/configlist' => array("module_rmb/account/configlist",'GET'),//金额规则
        'account/setconfig' => array("module_rmb/account/setconfig",'GET'),//规则
        'diamond/query'    =>array("module_rmb/account/sublist",'GET'),
        'account/query'=>array("module_rmb/account/query",'GET'),
        'GetAllChannelInfo' =>array("Manage/GetAgentInfoList",'POST'),//android sdk 渠道

        );

    //完成
    public static function excute($data=array(),$method="",$isList=true)
    {
       
        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF).self::$arr[$method][0],$data,self::$arr[$method][1]);
       /*  echo Config::get(self::API_URL_CONF).self::$arr[$method][0];
        var_dump($data);die();
        die(); */
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
        return array('success'=>false,'error'=>$res['errorDescription'],'data'=>false);
    }
    public static function v4excute($data=array(),$method="",$isList=true)
    {
       $res = Utility::loadByHttp(Config::get(self::API_URL_CONF).self::$arr[$method][0],$data,self::$arr[$method][1]);
         /*         echo Config::get(self::V4_API_URL_CONF).self::$arr[$method][0];
               var_dump($res);
                    */
//        echo Config::get(self::V4_API_URL_CONF).self::$arr[$method][0];
//        print_r($data);
//        print_r($res);
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
       //return array('success'=>false,'error'=>$res['errorDescription'],'data'=>false);
    }
    public static function v4GameExcute($data=array(),$method="",$isList=true)
    {
        
        $res = Utility::loadByHttp(Config::get(self::V4GAME_API_URL_CONF).self::$arr[$method][0],$data,self::$arr[$method][1]);
          return $res;
        /*  var_dump($res);
        die();  */
      /*   if(!$res)
            return array('success'=>false,'error'=>"接口不存在或参数错误",'data'=>false);
        if(!$res['errorCode']) {
            if($isList){
                if(isset($res['result']))
                    return array('success' => true, 'error' => false, 'data' => $res['result'],'count' => isset($res['totalCount'])?$res['totalCount']:0);
            }else{
                return array('success' => true, 'error' => false, 'data' => false);
            }
        }
        return array('success'=>false,'error'=>$res['errorDescription'],'data'=>false); */
    }
    public static function Excute3($data=array(),$method="",$isList=true)
    {
    
        $res = BaseHttp::http(Config::get(self::CHAN_API_URL_CONF).self::$arr[$method][0],$data,self::$arr[$method][1]);
//        echo Config::get(self::CHAN_API_URL_CONF).self::$arr[$method][0];
//        print_r($data);
//        print_r($res);
        return $res;
        /*  var_dump($res);
         die(); */ 
        /*   if(!$res)
         return array('success'=>false,'error'=>"接口不存在或参数错误",'data'=>false);
         if(!$res['errorCode']) {
         if($isList){
         if(isset($res['result']))
             return array('success' => true, 'error' => false, 'data' => $res['result'],'count' => isset($res['totalCount'])?$res['totalCount']:0);
             }else{
             return array('success' => true, 'error' => false, 'data' => false);
             }
             }
             return array('success'=>false,'error'=>$res['errorDescription'],'data'=>false); */
    }
    public  static  function GetAllChannelInfo (){
        $res = BaseHttp::http('test.www.365jiaoyi.com/GetAllChannelInfo',array(),'POST');
        return $res;
    }
  }