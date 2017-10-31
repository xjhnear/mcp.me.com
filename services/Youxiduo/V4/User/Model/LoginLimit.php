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
 * 登录限制模型类
 */
final class LoginLimit extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
    /**
	 * 
	 */
	public static function checkIsAllowLogin($limit_field,$limit_time,$limit_num,$login_name)
	{
		$ips = Config::get('app.allow_ip_list',array());
		if($limit_field && $ips && in_array($limit_field,$ips)) return true;
		if(!$limit_field) return true;
		$num = self::db()->where('limit_field','=',$limit_field)->where('limit_time','=',$limit_time)->where('type','=',1)->count();
		
		if($num>=$limit_num) return false;
		
		$exists = self::db()->where('limit_field','=',$limit_field)->where('limit_time','=',$limit_time)->where('type','=',1)->where('login_name','=',$login_name)->first();
		if($exists){
			$res = self::db()->where('id','=',$exists['id'])->increment('limit_num',1,array('lastupdatetime'=>time()));
		}else{
			$data = array('limit_field'=>$limit_field,'limit_time'=>$limit_time,'limit_num'=>1,'lastupdatetime'=>time(),'login_name'=>$login_name,'type'=>1);
			$res = self::db()->insert($data);
		}
		return true;
	}
}