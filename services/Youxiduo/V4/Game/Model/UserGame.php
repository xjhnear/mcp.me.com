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

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

use Youxiduo\Helper\Utility;
/**
 * 用户游戏模型类
 */
final class UserGame extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getGids($uid)
	{
		return self::db()->where('uid','=',$uid)->orderBy('sort','desc')->orderBy('id','desc')->lists('game_id');
	}
	
    public static function getMemberCount($game_id)
	{
		return self::db()->where('game_id','=',$game_id)->count();
	}
	
	public static function getMultiMemberCount($game_ids)
	{
		if(!$game_ids) return array();
		return self::db()->whereIn('game_id',$game_ids)->groupBy('game_id')->select(self::raw('game_id,count(*) as total'))->get();
	}
	
	public static function addUserGame($data)
	{
		return self::db()->insert($data);
	}
}