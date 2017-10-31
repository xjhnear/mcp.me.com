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
 * 新游预告模型类
 */
final class NewGame extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getList($pageIndex,$pageSize)
	{
		$result = self::db()->where('apptype','!=',1)->where('isshow','=',1)
			->orderBy('adddate','desc')
			->orderBy('sort','desc')
			->orderBy('id','desc')
			->forPage($pageIndex,$pageSize)
			->get();
		return $result;
	}
	
	public static function getCount()
	{
		return self::db()->where('apptype','!=',1)->where('isshow','=',1)->count();
	}

    public static function getShortInfoById($id){
        $fields = array('id','content','title','gname','pic');
        return self::db()->where('id',$id)->select($fields)->first();
    }

    public static function getShareInfoById($id){
        $fields = array('title', 'gname', 'pic');
        $info = self::db()->where('id',$id)->where(function($query){
            $query->where('apptype','&',2)->where('apptype','!=',0);
        })->select($fields)->first();

        return $info;
    }
}