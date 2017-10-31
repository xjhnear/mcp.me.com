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
 * 登录黑名单模型类
 */
final class LoginIpBlackList extends Model implements IModel
{	
	const LIMIT_TYPE_LOGIN = 1;
	const LIMIT_TYPE_REGISTER = 2;
	const LIMIT_TYPE_GIFTBAG = 3;
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function checkIsAllowLoginByIp($ip,$limit_time,$limit_num,$login_name='',$type=1)
	{		
		$ips = Config::get('app.allow_ip_list',array());
		if($ip && $ips && in_array($ip,$ips)) return true;
		if(!$ip) return true;
		$exists = self::db()->where('ip','=',$ip)->where('limit_time','=',$limit_time)->where('type','=',$type)->first();
		if($exists){
			$res = self::db()->where('ip','=',$ip)->where('limit_time','=',$limit_time)->where('limit_num','<',$limit_num)->where('type','=',$type)->increment('limit_num',1,array('lastupdatetime'=>time()));
			return $res > 0 ? true : false;
		}else{
			$data = array('ip'=>$ip,'limit_time'=>$limit_time,'limit_num'=>1,'lastupdatetime'=>time(),'login_name'=>$login_name,'type'=>$type);
			$res = self::db()->insert($data);
			return $res > 0 ? true : false;
		}
		return false;
	}
}