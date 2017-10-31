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
final class Video extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getCount($type)
	{
		$tb = self::db()->where('apptype','!=',1);
		if($type){
			$tb = $tb->where('type','=',$type);
		}
		return $tb->count();
	}
	
	public static function getList($pageIndex,$pageSize,$type)
	{
	    $tb = self::db()->where('apptype','!=',1);
		if($type){
			$tb = $tb->where('type','=',$type);
		}
		$result = $tb->orderBy('id','desc')->forPage($pageIndex,$pageSize)->get();
		return $result;
	}
	
	public static function getInfoById($id)
	{
		$info = self::db()->where('id','=',$id)->first();
		
		return $info;
	}
	
	public static function getPreId($id,$type)
	{
	    $tb = self::db()->where('apptype','!=',1);
		if($type){
			$tb = $tb->where('type','=',$type);
		}
		$pre_id = $tb->where('id','<',$id)->orderBy('id','desc')->select('id')->pluck('id');
		return $pre_id ? : 0;
	}
	
	public static function getNextId($id,$type)
	{
		$tb = self::db()->where('apptype','!=',1);
		if($type){
			$tb = $tb->where('type','=',$type);
		}
		$next_id = $tb->where('id','>',$id)->orderBy('id','asc')->select('id')->pluck('id');
		return $next_id ? : 0;
	}
	
	public static function updateViewTimes($vid,$num)
	{
		return self::db()->where('id','=',$vid)->increment('viewtimes',$num);
	}

    public static function getShareInfoById($id){
        $fields = array('vname','litpic', 'writer','gid');
        $info = self::db()->where('id','=',$id)->where(function($query){
            $query->where('apptype','&',2)->where('apptype','!=',0);
        })->select($fields)->first();
        return $info;
    }
}