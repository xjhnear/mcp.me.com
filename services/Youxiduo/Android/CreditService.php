<?php
/**
 * @package Youxiduo
 * @category Android 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */

namespace Youxiduo\Android;

use Youxiduo\Android\Model\CreditAccount;

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\BaseService;

class CreditService extends BaseService
{
	public static function doUserMoney($uid,$money,$experience,$action,$info='')
	{
		$userCredit = CreditAccount::db()->where('uid','=',$uid)->first();
	    $money_op_success = false;
		if($userCredit){
			if($money>0){
				$money_op_success = CreditAccount::db()->where('uid','=',$uid)->increment('money',$money)>0 ? true : false;
			}			
			if($money <= 0){
				$money_op_success = CreditAccount::db()->where('uid','=',$uid)->whereRaw('money>'.abs($money))->increment('money',$money)>0 ? true : false;
			} 
			if($experience != 0) CreditAccount::db()->where('uid','=',$uid)->increment('experience',$experience);
		}else{
			$data['money'] = $money;
			$data['experience'] = $experience;
			$data['uid'] = $uid;
			$money_op_success = CreditAccount::db()->insert($data);
		}
		if($money_op_success === false){
			return false;
		}
		if($money==0) return true;
				
		$credit_history = array('uid'=>$uid,'info'=>$info,'action'=>$action,'type'=>'游币','credit'=>$money,'mtime'=>(int)microtime(true));
		//self::dbClubMaster()->table('account_credit_history')->insert($credit_history);
		return true;
	}
}