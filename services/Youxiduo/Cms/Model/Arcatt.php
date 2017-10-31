<?php
/**
 * @package Youxiduo
 * @category Cms 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\Cms\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
/**
 * 模型类
 */
final class Arcatt extends Model implements IModel
{
	public static function getClassName()
	{
		return __CLASS__;
	}
	public static function getLists(){
		$result =  self::db() -> get();
		$out = array();
		foreach ($result as $v){
			$out[$v['attgroup']][] = $v;
		}
		return $out;
	}
}