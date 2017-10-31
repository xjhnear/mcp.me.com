<?php
/**
 * @package Youxiduo
 * @category Game 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\Game\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
/**
 * 模型类
 */
final class GameIos extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}	
}