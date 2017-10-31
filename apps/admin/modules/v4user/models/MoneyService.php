<?php
namespace modules\v4user\models;
use Youxiduo\Helper\Utility;
use modules\v4user\models\BaseHttp;
use Illuminate\Support\Facades\Config;

class MoneyService extends BaseHttp
{
    const API_URL = 'app.58080_api_url';
    const API_URL_2 = 'app.48080_api_url';

    /**
     * @param array $uids
     * @return array
     */
    public static function listYouMoney(array $uids)
    {
        $apiURL = Config::get(self::API_URL_2) . 'module_account/account/query';
        $params = array(
            'accountId'=>implode(',',$uids),
            'platform' => 'ios',
        );

        $result = self::http($apiURL,$params);
        if($result['errorCode']==0){
            $out = array();
            foreach($result['result'] as $row){
                $row['accountId'] = str_replace('ios','',$row['accountId']);
                $out[$row['accountId']] = $row['balance'];
            }
            return $out;
        }
        return array();
    }

    public static function listYouDiamond(array $uids)
    {
        $apiURL = Config::get(self::API_URL_2) . 'module_rmb/account/sublist';
        $params = array(
            'accountId'=>implode(',',$uids),
            'platform' => 'ios',
            'currencyType' => 1,
        );

        $result = self::http($apiURL,$params);
        if($result['errorCode']==0){
            $out = array();
            foreach($result['result'] as $row){
	    $row['accountId'] = str_replace('ios','',$row['accountId']);
                $out[$row['accountId']] = $row['balance'];
            }
            return $out;
        }
        return array();
    }
    
    public static function listYouMoneyios(array $uids)
    {
        $apiURL = Config::get(self::API_URL_2) . 'module_account/account/query';
        $params = array(
            'accountId'=>implode(',',$uids),
            'platform'=>'ios'
        );
    
        $result = self::http($apiURL,$params);

        if($result['errorCode']==0){
            $out = array();
            foreach($result['result'] as $row){
                $row['accountId'] = str_replace('ios', '', $row['accountId']);
                $out[$row['accountId']] = $row['balance'];
            }
            return $out;
        }
        return array();
    }
    
    public static function listYouDiamondios(array $uids)
    {
        $apiURL = Config::get(self::API_URL_2) . 'module_rmb/account/sublist';
        $params = array(
            'accountId'=>implode(',',$uids),
            'platform'=>'ios',
            'currencyType' => 1,
        );
    
        $result = self::http($apiURL,$params);
        if($result['errorCode']==0){
            $out = array();
            foreach($result['result'] as $row){
                $row['accountId'] = str_replace('ios', '', $row['accountId']);
                $out[$row['accountId']] = $row['balance'];
            }
            return $out;
        }
        return array();
    }

    public static function closeUser($uid)
    {
        $apiUrl = Config::get(self::API_URL) . 'module_forum/deactivate_account';
        $params = array(
            'uid'=>$uid
        );
        $result = self::http($apiUrl,$params);
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }

    public static function del_game_file($uid,$type="true")
    {
        $apiUrl = Config::get(self::API_URL) . 'module_adapter_other/relevance/del_game_file';
        $params = array(
            'uid'=>$uid,
            'isActive' => $type
        );
        $result = Utility::loadByHttp($apiUrl,$params,"POST");
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }

    public static function listDiamondOperation($search,$page,$pagesize)
    {
        $apiURL = Config::get(self::API_URL_2) . 'module_rmb/account/operation_query';
        $params = array(
            'currencyType'=>1,
            'platform' => 'ios',
            'pageIndex' => $page,
            'pageSize' => $pagesize,
        );
        if (isset($search['keyword'])) {
            $params['accountId'] = $search['keyword'];
        }
        if (isset($search['startdate']) && $search['startdate']<>"") {
            $params['operationTimeBegin'] = $search['startdate'].' 00:00:00';
        } else {
            $params['operationTimeBegin'] = date('Y-m-d H:i:s', strtotime("-30 day"));
        }
        if (isset($search['enddate']) && $search['enddate']<>"") {
            $params['operationTimeEnd'] = $search['enddate'].' 23:59:59';
        } else {
            $params['operationTimeEnd'] = date('Y-m-d H:i:s', time());
        }
//     print_r($params);exit;
        $result = self::http($apiURL,$params);
        
        if($result['errorCode']==0){
            $out = array();
            foreach($result['result'] as &$row){
                $row['accountId'] = str_replace('ios','',$row['accountId']);
                $row['operationTime'] = date('Y-m-d H:i:s', floor($row['operationTime']/1000));
                $row['operator'] = isset($row['operator'])?$row['operator']:'';
            }
            $out['data'] = $result['result'];
            $out['totalcount'] = $result['totalCount'];
            return $out;
        }
        return array();
    }

    public static function getCurrencyHistory($search,$pageIndex,$pageSize)
    {
        $url = Config::get('app.48080_api_url') . 'module_account/account/operation_query';
        $search['pageIndex'] = $pageIndex;
        $search['pageSize'] = $pageSize;
        $result = Utility::loadByHttp($url, $search, 'GET');
        if($result && $result['errorCode']=='0'){
            return $result;
        }
    }

    public static function getDiamondsHistory ($search,$pageIndex,$pageSize) {
        $url = Config::get('app.48080_api_url') . 'module_rmb/account/operation_query';
        $search['pageIndex'] = $pageIndex;
        $search['pageSize'] = $pageSize;
        $result = Utility::loadByHttp($url, $search, 'GET');
        if($result && $result['errorCode']=='0'){
            return $result;
        }
    }
}