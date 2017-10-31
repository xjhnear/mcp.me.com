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
 * 游戏专题类型模型类
 */
final class GameCollectType extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getList($platform)
	{
		$result = self::db()->where('platform','=',$platform)->orderBy('sort','desc')->orderBy('type_id','desc')->get();
		return $result;
	}
}