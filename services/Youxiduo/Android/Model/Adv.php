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
 * 广告模型类
 */
final class Adv extends Model implements IModel
{	
	public static function getClassName()
	{
		return __CLASS__;
	}

    public static function getInfoByGid($gid)
    {
        $fields = array('tab');
        return self::db()->where('link_id','=',$gid)->where('type',1)->where('appname','yxdandroid')->select($fields)->first();
    }

    public static function getList($appname,$version,$limit)
    {
        $result = self::db()
            ->where('appname','=',$appname)
            ->where('version','=',$version)
            ->orderby('sort','desc')
            ->forPage(1,$limit)
            ->get();

        return $result;
    }
}