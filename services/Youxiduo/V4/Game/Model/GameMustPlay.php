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
 * 游戏模型类
 */
final class GameMustPlay extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getList($platform,$pageIndex,$pageSize)
	{
		$result = self::buildSearch($platform)		
		->orderBy('sort','desc')
		->orderBy('addtime','desc')
		->forPage($pageIndex,$pageSize)
		->get();
		return $result;
	}
	
	public static function getCount($platform)
	{
		return self::buildSearch($platform)->count();
	}
	
	protected static function buildSearch($platform)
	{
		$tb = self::db();
		if($platform=='ios'){
			$tb = $tb->where('gid','>',0);
		}elseif($platform=='android'){
			$tb = $tb->where('agid','>',0);
		}
		return $tb;
	}
}