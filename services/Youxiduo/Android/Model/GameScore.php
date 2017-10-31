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
 * 游戏评分模型类
 */
final class GameScore extends Model implements IModel
{	
	public static function getClassName()
	{
		return __CLASS__;
	}

    public static function getCountByGameIds($gids)
    {
        if(!$gids) return array();
        return self::db()->whereIn('agid',$gids)->groupBy('agid')->select(self::raw('agid as gid,count(*) as total'))->lists('total','gid');
    }


    /**
     * 用户对游戏评分
     */
    public static function getScoreByUid($uid,$gid)
    {
        $fields = array('score');
        return self::db()->where('uid','=',$uid)->where('agid','=',$gid)->first();
    }
}