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
 * 用户礼包预约模型类
 */
final class UserGameReserve extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
    public static function getGids($uid)
	{
		return self::db()->where('uid','=',$uid)->orderBy('addtime','desc')->lists('game_id');
	}
	
	public static function addGameReserve($uid,$game_id)
	{
		$data = array();
		$data['uid'] = $uid;
		$data['game_id'] = $game_id;
		$data['addtime'] = time();
		return self::db()->insert($data);
	}
}