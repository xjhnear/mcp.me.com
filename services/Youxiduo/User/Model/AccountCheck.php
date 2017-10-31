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
namespace Youxiduo\User\Model;

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

use Youxiduo\Helper\Utility;
/**
 * 账号模型类
 */
final class AccountCheck extends Model implements IModel
{

    public static function getClassName()
    {
        return __CLASS__;
    }

    /**
     * 註冊用户驗證手机(一個月內只能註冊2次)
     * @param $phone ipfa机器码
     * @return $return true是 false否
     */
    public static function checkPhoneTime($phone)
    {
        $res = false;
        $time = date("Ym",time());
        $data['time'] = $time;
        $data['phone'] = $phone;
       if(!empty($phone)&&!empty($time)){
           $tb = self::db()->select(array('num','id'))->where('phone','=',$phone)->where('time','=',$time)->orderBy('time','desc')->first();
           if(isset($tb['id'])&&$tb['id']){
               if($tb['num']<2){
                   $data['num'] = $tb['num']+1;
                   $result = self::db()->select(array('num'))->where('phone','=',$phone)->where('time','=',$time)->update($data);
                   if($result){
                       $res = true;
                   }
               }
           }else{
               $data['num'] = 1;
               $result = self::db()->insertGetId($data);
               if($result){
                   $res = true;
               }
           }
       }
        return $res;
    }

    /**
     * 判断每个月2次是否存在
     * @param $phone ipfa机器码
     * @return $return true是 false否
     */
    public static function checkPhoneExist($phone)
    {
        $res = false;
        $time = date("Ym",time());
        if(!empty($phone)&&!empty($time)) {
            $tb = self::db()->select(array('num', 'id'))->where('phone', '=', $phone)->where('time', '=', $time)->first();
            if(!isset($tb['id'])||empty($tb['id'])){
                $res = true;
            }
            if(isset($tb['num'])&&$tb['num']<2){
                $res = true;
            }
        }
        return $res;
    }

}