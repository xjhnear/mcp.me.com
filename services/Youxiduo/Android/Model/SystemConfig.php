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
 * 配置模型类
 */
final class SystemConfig extends Model implements IModel
{	
	public static function getClassName()
	{
		return __CLASS__;
	}

    /**
     * 获取ip黑名单
     */
    public static function getIpBlackList()
    {
        return self::db()
            ->where('varname','=','comment_ip_blacklist')
            ->where('typeid','=',0)
            ->where('value','!=','')
            ->select('value')->first();
    }

    /**
     * 获取下载平台描述
     */
    public static function getDownloadPlatDesc()
    {
        return self::db()
            ->where('varname','=','android_downloadplat_desc')
            ->where('typeid','=',1)
            ->where('value','!=','')
            ->select('value')->first();
    }
}