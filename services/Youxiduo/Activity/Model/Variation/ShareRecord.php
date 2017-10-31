<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/20
 * Time: 15:57
 *//**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/3/31
 * Time: 11:27
 */
namespace Youxiduo\Activity\Model\Variation;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

class ShareRecord extends Model implements IModel
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    public static function saveData($sharecode,$codesource)
    {
        $exists = self::db()->where('sharecode','=',$sharecode)->first();
        if($exists) return true;
        $res = self::db()->insertGetId(array('sharecode'=>$sharecode,'codesource'=>$codesource,'create_time'=>time()));
        return $res ? true : false;
    }

    public static function getInfo($sharecode)
    {
        return self::db()->where('sharecode','=',$sharecode)->first();
    }
}