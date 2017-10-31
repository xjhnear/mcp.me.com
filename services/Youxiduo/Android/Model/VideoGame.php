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
 * 游戏视频游戏模型类
 */
final class VideoGame extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	
	public static function getGameIdsByVideoId($vid,$limit=6)
	{
		$gids = self::db()->where('vid','=',$vid)->where('agid','>',0)->orderBy('id','asc')->forPage(1,$limit)->lists('agid');
		
		return $gids;
	}

    /**
     * 游戏文章是否包含视频
     */
    public static function _isExistVideo($content){
        if (strpos($content, "youku.com")){
            return true;
        }
        return false;
    }

}