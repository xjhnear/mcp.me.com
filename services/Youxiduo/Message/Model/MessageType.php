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
namespace Youxiduo\Message\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
/**
 * 系统消息类型模型类
 */
final class MessageType extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}	
	
    public static function getList()
	{
		$result = self::db()->orderBy('id','asc')->get();
		$out = array();
		foreach($result as $row){
			$out[$row['typename']] = $row;
		}
		return $out;
	}
}