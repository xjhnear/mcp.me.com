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
 * 推荐游戏模型类
 */
final class GameRecommend extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
    public static function getList($type,$pageIndex,$pageSize)
	{
		$result = self::db()
		    ->where('type','=',$type)->where('agid','>',0)
		    ->orderBy('sort','desc')
		    ->forPage($pageIndex,$pageSize)
		    ->get();
		
		return $result;
	}
}