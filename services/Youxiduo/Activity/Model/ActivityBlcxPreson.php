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

class ActivityBlcxPreson extends Model implements IModel
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    /**
     * 添加月儿海选
     * @param $data
     * @return mixed
     */
    public static function add($data){
        return self::db()->insertGetId($data);
    }

    /**
     * 获取月儿列表
     * @return mixed
     */
    public static function getLists($audit=1){
        $tb = self::db();
        if($audit != -1){
            $tb = $tb->where('audit',$audit);
        }
        return $tb->orderBy('votes','desc')->get();
    }

    public static function getFirst($id){
        if(!$id) return false;
        return self::db()->where('id',$id)->first();
    }

    /**
     * 票数+1
     * @param $id
     * @return mixed
     */
    public static function setVotes($id){
        return self::db()->where('id',$id)->increment('votes');
    }

    //审核
    public static function save($data){
        if(!isset($data['id'])) return false;
        $id = $data['id'];
        unset($data['id']);
        return self::db()->where('id',$id)->update($data);
    }

}