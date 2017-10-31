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
namespace Youxiduo\Adv\Model;

use Youxiduo\Base\MyBaseModel;
use Youxiduo\Base\IModel;
final class Location extends MyBaseModel implements IModel{
    
    public static function getClassName(){
    	return __CLASS__;
    }
}