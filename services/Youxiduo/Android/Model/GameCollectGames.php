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
 * 游戏专题游戏模型类
 */
final class GameCollectGames extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
		
    public static function getListById($zt_id)
	{
		$result = self::db()->where('zt_id','=',$zt_id)->where('agid','>',0)->get();
		return $result;
	}
	
    public static function getListByIds($zt_ids)
	{
		if(!$zt_ids) return array();
		$result = self::db()->whereIn('zt_id',$zt_ids)->where('agid','>',0)->orderBy('id','asc')->get();
		return $result;
	}
}