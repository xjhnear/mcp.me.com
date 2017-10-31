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
 * 图片广告模型类
 */
final class AdvAppLink extends Model implements IModel
{	
	public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getVersionsByAdvId($adv_id,$place_type_badge)
	{
		return self::db()->where('adv_id','=',$adv_id)->where('place_type_badge','=',$place_type_badge)->lists('version');
	}
	
	public static function saveAdvAppVersion($adv_id,$place_type_badge,$appname,$channel,$versions)
	{
		self::db()->where('adv_id','=',$adv_id)->where('place_type_badge','=',$place_type_badge)->delete();
		$data = array();
		foreach($versions as $version){
			$data[] = array('adv_id'=>$adv_id,'place_type_badge'=>$place_type_badge,'appname'=>$appname,'channel'=>$channel,'version'=>$version,'create_at'=>date('Y-m-d H:i:s'));
		}
		if(!empty($data)){
			return self::db()->insert($data); 
		}
		return false;
	}
	
	public static function getAdvIds($place_type_badge,$appname,$channel,$version)
	{
		return self::db()->where('place_type_badge','=',$place_type_badge)		    
	        ->where('appname','=',$appname)
	        ->where('channel','=',$channel)
	        ->where('version','=',$version)
	        ->lists('adv_id');
	}
}