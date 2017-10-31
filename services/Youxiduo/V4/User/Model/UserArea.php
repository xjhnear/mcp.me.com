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
namespace Youxiduo\V4\User\Model;

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

use Youxiduo\Helper\Utility;
/**
 * 用户地区模型类
 */
final class UserArea extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getArea($uid)
	{
		$info = self::db()->where('uid','=',$uid)->first();
		
		return $info;
	}
	
	/**
	 * 修改用户所属地区
	 */
	public static function updateArea($uid,$province,$city,$region,$address)
	{
		$data = array();
		$data['country'] = '0';
		$data['province'] = $province;
		$data['city'] = $city;
		$data['regin'] = $region;
		$data['address'] = $address;
		$data['updatetime'] = time();
		
		$exists = self::db()->where('uid','=',$uid)->update($data);
		if(!$exists){
			$data['uid'] = $uid;
			return self::db()->insert($data) ? true : false;
		}
		return true;
	}
}