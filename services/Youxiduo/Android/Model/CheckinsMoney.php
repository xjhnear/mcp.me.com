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
/**
 * 签到游币统计模型类
 */
final class CheckinsMoney extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}	
	
	public static function getTodayMoney($uid)
	{
		return self::db()->where('uid','=',$uid)->where('checkins_date','=',date('Ymd'))->sum('score');
	}
	
    public static function getTodayMoneyList($uid)
	{
		return self::db()->where('uid','=',$uid)->where('checkins_date','=',date('Ymd'))->get();
	}
	
	public static function addTodayMoney($uid,$type,$money)
	{
		$data = array(
		    'uid'=>$uid,'type'=>$type,'score'=>$money,'checkins_date'=>date('Ymd')
		);
		return self::db()->insertGetId($data);
	}
}