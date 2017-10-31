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
namespace Youxiduo\Android\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

use Youxiduo\Helper\Utility;
/**
 * 账号模型类
 */
final class CreditAccount extends Model implements IModel
{		
    public static function getClassName()
	{
		return __CLASS__;
	}	
	
	/**
	 * 获取用户的财富数据
	 */
	public static function getUserCreditByUid($uid)
	{
		$credit = array('money'=>0,'experience'=>0);
		$data = self::db()->where('uid','=',$uid)->first();
		if($data){
			$credit['money'] = $data['money'];
			$credit['experience'] = $data['experience'];
		}
		return array($uid=>$credit);
	}
	
    /**
	 * 获取用户的财富数据
	 */
	public static function getUserCreditByUids($uids)
	{
		if(!$uids) return array();
		$credit = array('money'=>0,'experience'=>0);
		$result = self::db()->whereIn('uid',$uids)->get();
		$user_credits = array();
		foreach($result as $row){
			$user_credits[$row['uid']] = $row;			
		}
		$out = array();
		foreach($uids as $uid){
			if(isset($user_credits[$uid])){
				$out[$uid] = array('money'=>$user_credits[$uid]['money'],'experience'=>$user_credits[$uid]['experience']);
			}else{
				$out[$uid] = $credit;				
			}
		}
		return $out;
	}
	
	public static function updateUserExperience($uid,$experience)
	{
		$data = self::db()->where('uid','=',$uid)->first();
		if($data){
			$res = self::db()->where('uid','=',$uid)->increment('experience',$experience);
		}else{
			$res = self::db()->insert(array('uid'=>$uid,'money'=>0,'experience'=>$experience));
		}
		return $res ? true : false;
	}
	
}