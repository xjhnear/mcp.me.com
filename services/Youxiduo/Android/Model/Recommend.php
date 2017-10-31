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
 * 推荐模型类
 */
final class Recommend extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getList($pageIndex,$pageSize)
	{
		$result = self::db()->where('apptype','!=',1)->orderBy('sort','desc')->forPage($pageIndex,$pageSize)->get();
		
		return $result;
	}
}