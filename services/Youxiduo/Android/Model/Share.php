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
 * 分享模型类
 */
final class Share extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}

    public static function getDetailByTypeId($typeid){
        $fields = array('weibo','weixin');
        return self::db()->where('typeid',$typeid)->first();
    }
}