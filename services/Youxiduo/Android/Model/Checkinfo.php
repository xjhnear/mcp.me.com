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
 * 签到模型类
 */
final class Checkinfo extends Model implements IModel
{		
    public static function getClassName()
	{
		return __CLASS__;
	}	
	
    /**
	 * 连续签到记录
	 */
	public static function getContinuousCheckin($uid,$days=8)
	{
		$today_start = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$list = self::db()->where('uid','=',$uid)->orderBy('ctime','desc')->take($days)->lists('ctime');
		$count = count($list);
		$continuous = array();
		for($i=0;$i<$count;$i++){
			$continuous[] = $today_start - (60*60*24*($i+1));
		}
		$checkin_list = array();
		$index = 0;
		foreach($list as $time){
			if($time>=$today_start){
				$checkin_list[] = $time;
			}else{			
				if($time>=$continuous[$index]){
					$checkin_list[] = $time;
					$index++;
				}else{
				    break;
			    }
			}
		}
		return $checkin_list;
	}
	
	public static function getCurrentMonthCheckinsTimes($uid,$start_time=0)
	{
		if($start_time>0){
			$month_start = $start_time;
		}else{
		    $month_start = mktime(0,0,0,date('m'),1,date('Y'));
		}
		return self::db()->where('uid','=',$uid)->where('ctime','>',$month_start)->count();		
	}
	
}