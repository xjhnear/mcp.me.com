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
 * 游戏模型类
 */
final class GameMustPlay extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getList($pageIndex,$pageSize)
	{
		$result = self::db()
		->where('agid','>',0)
		->orderBy('sort','desc')
		->orderBy('addtime','desc')
		->forPage($pageIndex,$pageSize)
		->get();
		return $result;
	}
	
	public static function getCount()
	{
		return self::db()->where('agid','>',0)->count();
	}
}