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
namespace Youxiduo\Game\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
/**
 * 游戏包模型类
 */
final class GamePicture extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getListByGameId($game_id)
	{
		return self::db()->where('type','=','android')->where('game_id','=',$game_id)->get();
	}
	
	public static function m_save($data)
	{
		return self::db()->insert($data);
	}
	
	public static function m_delete($id=0,$game_id=0,$litpic='')
	{
		if($id){
			return self::db()->where('id','=',$id)->delete();
		}elseif($game_id){
			return self::db()->where('game_id','=',$game_id)->where('type','=','android')->delete();
		}elseif($litpic){
			return self::db()->where('litpic','=',$litpic)->delete();
		}
	}
}