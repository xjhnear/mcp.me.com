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

use Youxiduo\Helper\Utility;
/**
 * 账号模型类
 */
final class CreditLevel extends Model implements IModel
{		
    public static function getClassName()
	{
		return __CLASS__;
	}

	public static function getLevelList()
	{
		$result = self::db()->orderBy('start','asc')->get();
		return $result;
	}
	
	public static function getUserLevel($experience)
	{
		$levels = self::getLevelList();
		foreach($levels as $level){
			if($experience >= $level['start'] && $experience < $level['end']){
				return $level;
			}
		}
		return $levels[0];
	}
}