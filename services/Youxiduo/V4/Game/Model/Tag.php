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
use Youxiduo\Base\MyBaseModel; 
use Youxiduo\Base\IModel;
/**
 * 标签模型类
 */
final class Tag extends MyBaseModel implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
    public static function getListByType($type_id)
	{
		$tb = self::db();
		if($type_id){
			$tb = $tb->where('typeid','=',$type_id);
		}
		return $tb->get();
	}
}