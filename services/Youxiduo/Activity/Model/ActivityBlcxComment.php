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

class ActivityBlcxComment extends Model implements IModel
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    /**
     * 添加评论
     * @param $data
     * @return mixed
     */
    public static function add($data){
        return self::db()->insertGetId($data);
    }

    public static function getLists($audit = 1){
        $tb = self::db();
        if($audit != -1){
            $tb = $tb->where('audit',$audit);
        }
        return $tb->orderBy('addtime','desc')->get();
    }
    //修改留言
    public static function audit($id , $data){
        return self::db()->where('id',$id)->update($data);
    }

    /**
     * 检查是否留言
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