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
 * 用户验证码模型类
 */
final class UserVerifyCode extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function checkEmailAndVerifyCode($email,$verifycode)
	{
		$info = self::db()->where('email','=',$email)
		->where('verifycode','=',$verifycode)
		//->where('is_send_msg','=',0)
		->where('is_valid','=',1)
		->first();
		if($info && isset($info['uid']) && $info['uid']){			
			return $info['uid'];
		}
		return 0;
	}
	
	public static function validVerifyCode($uid)
	{
		return self::db()->where('uid','=',$uid)->update(array('is_valid'=>0,'update_time'=>time()));
	}
}