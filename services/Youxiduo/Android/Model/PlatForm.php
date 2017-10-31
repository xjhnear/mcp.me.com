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
 * 游戏平台模型类
 */
final class PlatForm extends Model implements IModel
{	
	public static function getClassName()
	{
		return __CLASS__;
	}

    public static function getListByIds(array $ids)
    {
        if(!$ids) return array();
        $fields = array('id','platname');
        return self::db()->whereIn('id',$ids)->select($fields)->get();
    }
}