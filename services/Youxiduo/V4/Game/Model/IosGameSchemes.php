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

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

use Youxiduo\Helper\Utility;
/**
 * 游戏模型类
 */
final class IosGameSchemes extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getSchemesToKeyValue($gids)
	{
		if(!$gids) return array();
		if(!is_array($gids)) $gids = array($gids);
		$result = self::db()->whereIn('gid',$gids)->lists('schemesurl','gid');
		return $result;
	}
}