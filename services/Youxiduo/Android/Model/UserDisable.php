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
/**
 * 禁言用户模型类
 */
final class UserDisable extends Model implements IModel
{	
    public static function getClassName()
{
    return __CLASS__;
}

    public static function getInfoById($id)
    {
        return self::db()->where('uid','=',$id)->where('isopen','=',1)->first();
    }
}