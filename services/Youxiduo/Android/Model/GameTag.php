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
 * 游戏标签模型类
 */
final class GameTag extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
    public static function getGameByTag($tag)
    {
        return self::db()->where('tag',$tag)->where('agid','>',0)->select('agid')->lists('agid');
    }

    public static function getListByGids($gids){
        if(!$gids) return array();
        return self::db()->whereIn('agid',$gids)->select('agid','tag')->orderby('agid','asc')->get();
    }

    public static function getDetailByGid($gid)
    {
        $fields = array('tag');
        return self::db()->where('agid','=',$gid)->select($fields)->get();
    }
}