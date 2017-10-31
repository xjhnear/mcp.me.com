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
 * 游戏区服模型类
 */
final class GameArea extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getGameAreaList($game_id,$platform)
	{
		$res = self::db()->where('game_id','=',$game_id)->where('platform','=',$platform)->orderBy('sort','asc')->orderBy('id','asc')->get();
		
		return $res;
	}
}