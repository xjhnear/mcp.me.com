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

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

final class AccountReceiptAddress extends Model implements IModel
{

    public static function getClassName()
    {
        return __CLASS__;
    }

    //增
    public static function addReceiptAddress ($data = array()) {
        if (!$data) return false;
        $id = self::db()->insertGetId($data);
        return $id ? true : false;
    }

    //删
    public static function delReceiptAddress ($addressId = false) {
        if (!$addressId) return false;
        $count = self::db()->where('id', '=', $addressId)->delete();
        return $count > 0 ? true : false;
    }

    //改
    public static function updateReceiptAddress ($addressId = false, $data = array()) {
        if (!$addressId || !$data) return false;
        $res = self::db()->where('id', '=', $addressId)->update($data);
        return $res ? true : false;
    }

    //查
    public static function searchReceiptAddress ($uId = false, $is_default = 'all') {
        if (!$uId) return false;
        $res = self::db()->where('uid', '=', $uId);
        if ($is_default != 'all') {
            $res->where('is_default', '=', $is_default);
        }
        return $res->get();
    }

    //修改默认收货地址
    public static function updateDefaultAddress ($addressId = false, $uId = false) {
        if (!$addressId || !$uId) return false;
        self::emptyDefaultAddress($uId);
        $res = self::db()->where('id', '=', $addressId)->update(array('is_default'=>1));
        return $res ? true : false;
    }

    public static function emptyDefaultAddress ($uId = false) {
        if(!$uId) return false;
        self::db()->where('uid', '=', $uId)->update(array('is_default'=>0));
        return true;
    }

    public static function checkDefaultAddress ($uId = false) {
        if (!$uId) return false;
        $count = self::db()->where('uid', '=', $uId)->where('is_default', '=', '1')->count();
        return $count>0 ? true : false;
    }

}