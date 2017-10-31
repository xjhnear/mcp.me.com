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
 * 第三方账号登录模型类
 */
final class ThirdAccountLogin extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getUserByToken($type,$access_token)
	{
		$info = self::db()->where('type','=',$type)->where('access_token','=',$access_token)->first();
		if(!$info) return false;
		return $info['uid'];
	}
	
    public static function getUserByUserId($type,$user_id)
	{
		$info = self::db()->where('type','=',$type)->where('type_uid','=',$user_id)->first();
		if(!$info) return false;
		return $info['uid'];
	}
	
	public static function bindThirdUser($uid,$type,$access_token,$user_id)
	{
		$data = array('uid'=>$uid,'type'=>$type,'type_uid'=>$user_id,'access_token'=>$access_token);
		return self::db()->insert($data) ? true : false;
	}
}