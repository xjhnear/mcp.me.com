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
namespace Youxiduo\V4\Cms\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
/**
 * 游戏专题游戏模型类
 */
final class Video extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getCount($platform,$type,$isTop)
	{
		return self::buildCond($platform,$type,$isTop)->count();
	}
	
	public static function getList($platform,$type,$isTop,$pageIndex,$pageSize,$sort)
	{
		$field = 'id';
		if($sort=='date') $field = 'addtime';
		if($sort=='hot') $field = 'viewtimes';
		$result = self::buildCond($platform,$type,$isTop)->orderBy($field,'desc')->orderBy('sort','desc')->orderBy('id','desc')->forPage($pageIndex,$pageSize)->get();
		return $result;
	}
	
	protected static function buildCond($platform,$type,$isTop=false)
	{
		$tb = self::db();
		if($platform=='ios'){
			$tb = $tb->where('apptype','!=',2);
		}elseif($platform=='android'){
			$tb = $tb->where('apptype','!=',1);
		}
		
		if($type){
			$tb = $tb->where('type','=',$type);
		}
		
		if($isTop){
			$tb = $tb->where('isapptop','=',$isTop);
		}
		
		return $tb;
	}
	
	public static function getInfoById($platform,$id)
	{
		$info = self::buildCond($platform,0)->where('id','=',$id)->first();
		
		return $info;
	}
	
	public static function getPreId($platform,$id,$type)
	{
	    
		$pre_id = self::buildCond($platform,$type)->where('id','<',$id)->orderBy('id','desc')->select('id')->pluck('id');
		return $pre_id ? : 0;
	}
	
	public static function getNextId($platform,$id,$type)
	{
		$next_id = self::buildCond($platform,$type)->where('id','>',$id)->orderBy('id','asc')->select('id')->pluck('id');
		return $next_id ? : 0;
	}
	
	public static function updateViewTimes($vid,$num)
	{
		return self::db()->where('id','=',$vid)->increment('viewtimes',$num);
	}
}