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
namespace Youxiduo\V4\Cms\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
/**
 * 游戏视频游戏模型类
 */
final class VideoGame extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getGameIdByVid($platform,$vid)
	{
		$field = $platform=='ios' ? 'gid' : 'agid';
		$gid = self::db()->where('vid','=',$vid)->where($field,'>',0)->pluck($field);
		return $gid;
	}
}