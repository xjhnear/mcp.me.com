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
 * 游戏视频分类模型类
 */
final class VideoType extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	
	public static function getList($platform,$isTop)
	{
		$tb = self::db();//->where('platform','=',$platform);
		
		if($isTop){
			$tb = $tb->where('isTop','=',1);
		}		
		
		$result = $tb->orderBy('sort','desc')->get();
		
		return $result;
	}

}