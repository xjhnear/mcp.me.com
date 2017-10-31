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

class ActivityBlcxVote extends Model implements IModel
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    /**
     * 添加投票
     * @param $data
     * @return mixed
     */
    public static function add($data){
        return self::db()->insertGetId($data);
    }

    /**
     * 检查是否投票
     * @param $uid
     * @return mixed
     */
    public static function check($uid){
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday=mktime(23,59,59,date('m'),date('d'),date('Y'));
        $result = self::db()->where('uid',$uid)->whereBetween('addtime',array($beginToday,$endToday))->get();
        return $result;
    }

}