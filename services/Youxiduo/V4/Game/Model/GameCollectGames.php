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
 * 游戏专题游戏模型类
 */
final class GameCollectGames extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
		
    public static function getListById($platform,$zt_id)
	{
		$result = self::buildSearch($platform, array($zt_id))->get();
		return $result;
	}
	
    public static function getListByIds($platform,$zt_ids)
	{
		if(!$zt_ids) return array();
		$result = self::buildSearch($platform, $zt_ids)->orderBy('id','asc')->get();
		return $result;
	}
	
    protected static function buildSearch($platform,$zt_ids)
	{
		$tb = self::db();
		if($platform=='ios'){
			$tb = $tb->whereIn('zt_id',$zt_ids)->where('gid','>',0);
		}elseif($platform=='android'){
			$tb = $tb->whereIn('zt_id',$zt_ids)->where('agid','>',0);
		}
		return $tb;
	}
}