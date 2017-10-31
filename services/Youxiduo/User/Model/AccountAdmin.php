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
namespace Youxiduo\User\Model;

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

use Youxiduo\Helper\Utility;
/**
 * 账号模型类
 */
final class AccountAdmin extends Model implements IModel
{

    public static function getClassName()
    {
        return __CLASS__;
    }

	public static function isAdmin($uid)
	{
		$info = self::db()->where('uid','=',$uid)->first();
		if($info){
			return true;
		}
		return false;
	}
	
	public static function insertAdmin($uid)
	{
	    $info = self::db()->where('uid','=',$uid)->first();
	    if($info){
	        return false;
	    }else{
	        $data = array();
	        $data['uid'] = $uid;
	        $res = self::db()->insertGetId($data);
	    }
	    return $res ? true : false;
	}

}