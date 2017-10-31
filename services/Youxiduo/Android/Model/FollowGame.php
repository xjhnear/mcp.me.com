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
use Illuminate\Support\Facades\Config;
use Youxiduo\Helper\Utility;
/**
 * 关注游戏模型类
 */
final class FollowGame extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}	
}