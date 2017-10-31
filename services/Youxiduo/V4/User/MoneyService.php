<?php
/**
 * @package Youxiduo
 * @category Base 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\V4\User;

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Youxiduo\User\Model\Account;
use Youxiduo\V4\User\Model\Money;
use Yxd\Services\CreditService;

class MoneyService extends BaseService
{
	const ERROR_PARAMS_MISS = 'params_miss';
	/**
	 * 处理用户游币/经验
	 * @deprecated
	 * @param int $uid
	 * @param int $money
	 * @param int $experience
	 * @param string $action
	 * @param string $info
	 * 
	 * 
	 * @return bool 
	 */
	public static function doCredit($uid,$money,$experience, $action,$info)
	{
		if(!$uid || !$money) return self::ERROR_PARAMS_MISS;
		$success = CreditService::handOpUserCredit($uid, $money, $experience, $action,$info);
		return $success;
	}
	
	/**
	 * 查询行为奖励
	 */
	public static function getActionReward()
	{
		
	}
	
	/**
	 * 添加行为奖励
	 */
	public static function addActionReward()
	{
		
	}
	
	/**
	 * 修改行为奖励
	 */
    public static function updateActionReward()
	{
		
	}
	
	/**
	 * 注册账号
	 */
	public static function registerAccount($uid,$platform='android')
	{
		$params = array(
		    'id'=>$uid,
		    'experience'=>0,
		    'platform'=>$platform
		);
		$url = Config::get('app.account_api_url') . 'account/register';
		$result = Utility::loadByHttp($url,$params,'POST');
		if($result && $result['errorCode']=='0'){
			return true;
		}
		return false;
	}
	
	public static function checkAccount($uid,$platform)
	{
	    $params = array(
	        'accountId'=>$uid,
	        'currencyType'=>1,
	        'platform'=>$platform
	    );
	    $url = Config::get('app.account_api_url') . 'account/query';
	    $result = Utility::loadByHttp($url,$params,'GET');
	    if($result && $result['errorCode']=='0'){
	        self::registerAccount($uid,$platform);
	        return true;
	    }
	    return false;
	}

	/**
	 * 开通账号
	 */
	public static function openAccount($uid)
	{
		
	}
	
	/**
	 * 处理用户经验
	 */
	public static function doAccountExperience($uid,$experience)
	{
		$params = array('accountId'=>$uid,'experienceChange'=>$experience);
		$url = Config::get('app.account_api_url') . 'account/change_experience';
		$result = Utility::loadByHttp($url,$params,'GET');
		if($result && $result['errorCode']=='0'){
			return true;
		}
		return false;
	}

	/**
	 * 处理用户游币
	 */
	public static function doAccount($uid,$money,$type,$info='')
	{
		if(!$uid || $money==0) return false;
		$exists = Account::getUserInfoByField($uid,'uid');
		if(!$exists){
			return false;
		}elseif($exists['is_open_android_money']==0){
			self::registerAccount($uid);
		}
		$params = array(
		    'rechargeAccountId'=>$uid,
		    'balanceChange'=>$money,
		    'type'=>$type,
		    'operationInfo'=>$info
		);
		$url = Config::get('app.account_api_url') . 'account/updatebalance';
		$result = Utility::loadByHttp($url,$params,'GET');
		if($result && $result['errorCode']=='0'){
			return true;
		}
		return false;
	}

    /**
     * 处理用户人民币币
     */
    public static function doRmb($params)
    {
        if(!$params['rechargeAccountId'] || $params['balanceChange']==0) return false;
        $exists = Account::getUserInfoByField($params['rechargeAccountId'],'uid');
        if(!$exists){
            return false;
        }elseif($exists['is_open_android_money']==0){
            self::registerAccount($params['rechargeAccountId']);
        }
        $url = Config::get('app.48080_api_url') . 'module_rmb/account/updatebalance';
        $result = Utility::loadByHttp($url,$params,'GET');
        if($result && $result['errorCode']=='0'){
            return true;
        }
        return false;
    }
	
	public static function getQueryResult($uids)
	{
		if(!$uids) return array();
		$params = array('accountId'=>implode(',',$uids));
		$url = Config::get('app.account_api_url') . 'account/query';
		$result = Utility::loadByHttp($url,$params,'GET');
		if($result && $result['errorCode']=='0' && is_array($result['result'])){
			$out = array();
			foreach($result['result'] as $row){
				$out[$row['accountId']] = $row;
			}
			return $out;
		}
		return array();
	}
	
	public static function getHistory($search,$pageIndex,$pageSize)
	{
		$total = 0;
		$out = array();
		$url = Config::get('app.account_api_url') . 'account/';
		$params = array('accountId'=>$search['uid']);
		$result = Utility::loadByHttp($url.'operation_querynum',$params,'GET');
		if($result && $result['errorCode']=='0'){
			$total = $result['totalCount'];
		}
		$params['pageIndex'] = $pageIndex;
		$params['pageSize'] = $pageSize;
		$result = Utility::loadByHttp($url.'operation_query',$params,'GET');
	    if($result && $result['errorCode']=='0'){
			$out = $result['result'];
		}
		return array('result'=>$out,'total'=>$total);
	}

    public static function getRmbHistory($search,$pageIndex,$pageSize)
    {
        $total = 0;
        $out = array();
        $url = Config::get('app.48080_api_url') . 'module_rmb/account/';
        $params = $search;
        $result = Utility::loadByHttp($url.'operation_querynum',$params,'GET');
        if($result && $result['errorCode']=='0'){
            $total = $result['totalCount'];
        }
        $params['pageIndex'] = $pageIndex;
        $params['pageSize'] = $pageSize;
        $result = Utility::loadByHttp($url.'operation_query',$params,'GET');
        if($result && $result['errorCode']=='0'){
            return $result;
        }
    }
}