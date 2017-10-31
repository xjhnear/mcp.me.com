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
namespace Youxiduo\V4\Game\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
/**
 * 标签模型类
 */
final class Tag extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
    public static function getListByType($type_id)
	{
		return self::db()->where('typeid','=',$type_id)->get();
	}
}