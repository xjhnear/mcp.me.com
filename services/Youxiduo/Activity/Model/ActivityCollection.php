<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/3/31
 * Time: 11:27
 */
namespace Youxiduo\Activity\Model;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

class ActivityCollection extends Model implements IModel
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    /**
     * 查询大区信息
     * @param string $platform
     * @return mixed
     */
    public static function  getDistrict($platform='',$field=array()){
        $query = self::db()->select($field);
        if($platform != ''){
            $query->where('platform',$platform);
        }
        return $query->get();
    }


}
