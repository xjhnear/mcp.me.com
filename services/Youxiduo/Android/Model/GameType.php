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
 * 游戏类型模型类
 */
final class GameType extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getList()
	{
		$result = self::db()->orderBy('isapptop','desc')->orderBy('sort','desc')->orderBy('updatetime','desc')->orderBy('id','desc')->get();
		
		return $result;
	}

    public static function getInfoById($id)
    {	
        return self::db()->where('id','=',$id)->first();
    }
}