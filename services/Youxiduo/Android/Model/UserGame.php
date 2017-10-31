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
 * 用户游戏模型类
 */
final class UserGame extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getGameIdsByUid($uid)
	{
		$result = self::db()->where('uid','=',$uid)->orderBy('istop','desc')->orderBy('sort','desc')->orderBy('ctime','desc')->get();
		
		return $result;
	}
	
	public static function getAllUserId($game_id)
	{
	    $uids = self::db()->where('game_id','=',$game_id)->lists('uid');
		return $uids;
	}
	
	public static function addMyGame($uid,$gids)
	{
		if(!$uid || !$gids) return array();
		$gids = array_unique($gids);
		$old_gids = self::db()->where('uid','=',$uid)->lists('game_id');
		$new_gids = array_diff($gids,$old_gids);
		if($new_gids){
			$data = array();
			$ctime = time();
			foreach($new_gids as $game_id){
				$data[] = array('uid'=>$uid,'game_id'=>$game_id,'ctime'=>$ctime,'istop'=>0,'sort'=>0);
			}
			if($data){
				self::db()->insert($data);
			}
			return $new_gids;
		}
		return array();
	}
	
	public static function removeMyGame($uid,$gids)
	{
		if(!$gids) return false;
		$result = self::db()->where('uid','=',$uid)->whereIn('game_id',$gids)->delete();
		return $result;
	}
}