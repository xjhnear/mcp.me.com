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

class ActivityBlcxBag extends Model implements IModel
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    //获取剩余礼包个数
    public static function getCount(){
        return self::db()->where('is_send',0)->count();
    }

    //获取
    public static function getFirst($uid){
        $code = self::db()
            ->where('is_send',0)
            ->orWhere(function($query)use($uid){
                $query->where('uid', $uid)->where('is_send', 1);
            })
            ->first();
        //修改修改状态
        $data = array('id'=>$code['id'],'is_send'=>1,'uid'=>$uid);
        $re = self::save($data);
        if($re){
            return $code;
        } else{
            return false;
        }
    }


    //修改状态
    public static function save($data){
        if(!isset($data['id'])) return false;
        $id = $data['id'];
        unset($data['id']);
        return self::db()->where('id',$id)->update($data);
    }

}