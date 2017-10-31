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

class ActivityCollectionInfo extends Model implements IModel
{
    public static function getClassName()
    {
        return __CLASS__;
    }
    /**
     * 添加信息
     * @param array $data
     */
    public static function addInfo(array $data){
        return self::db()->insert($data);
    }


}
