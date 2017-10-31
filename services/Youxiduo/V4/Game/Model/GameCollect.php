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
 * 游戏专题模型类
 */
final class GameCollect extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getList($platform,$type_id,$pageIndex,$pageSize,$isTop=0)
	{
		$result = self::buildSearch($platform,$type_id,$isTop=0)
			->orderBy('isapptop','desc')
			->orderBy('addtime','desc')
			->forPage($pageIndex,$pageSize)
			->get();
		return $result;
	}	
	
	public static function getCount($platform,$type_id,$isTop=0)
	{
		return self::buildSearch($platform,$type_id,$isTop=0)->count();
	}
	
    protected static function buildSearch($platform,$type_id,$isTop=0)
	{
		$tb = self::db();
		if($platform=='ios'){
			$tb = $tb->where('apptype','!=',2);			
		}elseif($platform=='android'){
			$tb = $tb->where('apptype','!=',1);
		}
		
	    if($isTop){
		    $tb = $tb->where('isapptop','=',1);
		}
		
	    if($type_id){
		    $tb = $tb->where('type_id','=',$type_id);
		}
		
		return $tb;
	}
	
	public static function getInfoById($platform,$id)
	{
		$result = self::buildSearch($platform,0,0)->where('id','=',$id)->first();
		return $result;
	}
	
	public static function updateViewTimes($id,$num)
	{
		return self::db()->where('id','=',$id)->increment('viewtimes',$num);
	}
}