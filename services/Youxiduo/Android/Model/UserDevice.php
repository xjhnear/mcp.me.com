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
 * 用户设备模型类
 */
final class UserDevice extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}

    public static function getNewestInfoByUid($uid){
        if(!$uid) return false;
        if(is_array($uid)){
            $query = self::db();
            $result = array();
            $tmpresult = $query->whereIn('uid',$uid)->orderBy('update_time','desc')->get();
            if($tmpresult){
                foreach($tmpresult as $row){
                    if(isset($result[$row['uid']])) continue;
                    $result[$row['uid']] = $row;
                }
            }
            return $result;
        }else{
            return self::db()->where('uid',$uid)->orderBy('update_time','desc')->first();
        }
    }
}