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
 * 游戏包模型类
 */
final class GamePackage extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	/**
	 * 通过包名获取游戏列表
	 */
	public static function getGameListByPackage(array $pkg_list)
	{
		if(!$pkg_list) return array();
		$result = self::db()->whereIn('apk_package_name',$pkg_list)->get();
		
		return $result;
	}
	
    /**
	 * 通过game_id获取游戏列表
	 */
	public static function getGameListByGameId(array $gids)
	{
		if(!$gids) return array();
		$result = self::db()->whereIn('gid',$gids)->get();
		
		return $result;
	}

    public static function getGameOneByGameId(array $gids)
    {
        if(!$gids) return array();
        $result = self::db()->whereIn('gid',$gids)->orderby('apkplat_id','desc')->first();
        return $result;
    }
}