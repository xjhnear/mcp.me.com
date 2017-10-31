<?php
/**
 * @package Youxiduo
 * @category TOPic 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\Topic\Model;
use Youxiduo\Base\MyBaseModel;
use Youxiduo\Base\IModel;
final class Gametype extends MyBaseModel implements IModel{
    public static function getClassName(){
    	return __CLASS__;
    }
}