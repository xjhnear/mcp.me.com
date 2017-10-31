<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/4/15
 * Time: 16:08
 */
namespace Youxiduo\Activity\Model;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
use Illuminate\Support\Facades\DB;

class ActivityBlcxInfo extends Model implements IModel
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    /**
     * 添加获奖信息
     * @param $data
     * @return mixed
     */
    public static function add($data){
        return self::db()->insertGetId($data);
    }

}