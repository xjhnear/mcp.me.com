<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/4/22
 * Time: 18:05
 */

namespace Youxiduo\Activity\Model;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

class TtxdGirl extends Model implements IModel
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    /**
     * 修改票数
     * @param $data
     * @return mixed
     */
    public static function setIncrement($id)
    {
        return self::db()->where('id',$id)->increment('votes');
    }

    /**
     * 获取炫girl
     * @param $id
     * @return mixed
     */
    public static function getPreson($id = ''){
        if($id){
            return self::db()->where('id',$id)->first();
        }else{
            return self::db()->get();
        }

    }
}