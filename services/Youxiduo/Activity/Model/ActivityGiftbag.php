<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/4/14
 * Time: 10:38
 */

namespace Youxiduo\Activity\Model;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

class ActivityGiftbag extends Model implements IModel
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    /**
     * 获取列表
     * @return mixed
     */
    public static function getList($pagesize = 10,$page = 1 , $make = true , $tag=1){
        $out = array();
        $tb = self::db()->where('tag',$tag);
        if(!$make){
            $tb->where('gid','!=','');
        }
        $out['totalCount'] = $tb->count();
        $tb->forPage($page,$pagesize);
        $out['result'] = $tb->get();
        return $out;
    }

    /**
     * 获取详情
     * @param $id
     * @return bool
     */
    public static function getDetail($id){
        if(!$id) return false;
        $result = self::db()->where('id',$id)->first();
        return $result;

    }

    //修改状态
    public static function save(array $data){
        if(!$data) return false;
        if(empty($data['id'])){
            return self::db()->insertGetId($data);
        }else{
            $id = $data['id'];
            unset($data['id']);
            if(empty($data)) return false;
            return self::db()->where('id',$id)->update($data);
        }
    }

    public static function del($id){
        if(!$id) return false;
        return self::db()->where('id',$id)->delete();
    }

}