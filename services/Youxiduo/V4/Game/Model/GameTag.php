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
	
    public static function getGameTagsByGameIds($platform,$gids)
	{
		if(!$gids) return array();
		$tags = array();
		$_tags = array();
		if($platform=='ios'){
			$_tags = self::db()->whereIn('gid',$gids)->get();
			foreach($_tags as $row){
				$tags[$row['gid']][] = $row['tag'];
			}
		}elseif($platform=='android'){
			$_tags = self::db()->whereIn('agid',$gids)->get();
			foreach($_tags as $row){
				$tags[$row['agid']][] = $row['tag'];
			}
		}	    
		
		return $tags;
	}
}