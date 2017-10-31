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
 * 系统消息模板模型类
 */
final class MessageTpl extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}	
	
    public static function getList()
	{
		return self::db()->orderBy('id','asc')->get();
	}
	
	public static function getInfo($id)
	{
		return self::db()->where('id','=',$id)->first();
	}
	
	public static function save($data)
	{
		$ename = $data['ename'];
		$count = self::db()->where('ename','=',$ename)->count(); 
		if($count){			
			unset($data['ename']);
			unset($data['id']);
			return self::db()->where('ename','=',$ename)->update($data);
		}else{
			unset($data['id']);
			return self::db()->insertGetId($data);
		}
	}
}