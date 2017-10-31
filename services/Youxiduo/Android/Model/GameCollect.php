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
 * 游戏专题模型类
 */
final class GameCollect extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getList($pageIndex,$pageSize)
	{
		$result = self::db()->where('apptype','!=',1)
		->orderBy('isapptop','desc')
		->orderBy('addtime','desc')
		->forPage($pageIndex,$pageSize)
		->get();
		return $result;
	}	
	
	public static function getCount()
	{
		return self::db()->where('apptype','!=',1)->count();
	}
	
	public static function getInfoById($id)
	{
		$result = self::db()->where('id','=',$id)->first();
		return $result;
	}
	
	public static function updateViewTimes($id,$num)
	{
		return self::db()->where('id','=',$id)->increment('viewtimes',$num);
	}

    public static function getShareInfoById($id){
        $fields = array('ztitle' , 'writer', 'litpic');
        return self::db()->where('id',$id)->where(function($query){
            $query->where('apptype','&',2)->where('apptype','!=',0);
        })->select($fields)->first();
    }
}