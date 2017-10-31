<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/3/31
 * Time: 11:27
 */
namespace Youxiduo\Cms\Model;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

class VideosType extends Model implements IModel{
    public static function getClassName(){
        return __CLASS__;
    }

    public static function getInfo($type_id){
        if(!$type_id) return false;
        $query = self::db();
        if(is_array($type_id)){
            $query->whereIn('type_id',$type_id);
            return $query->get();
        }else{
            $query->where('type_id',$type_id);
            return $query->first();
        }
    }

    public static function getList($offset=1,$limit=10){
        return self::db()->forPage($offset,$limit)->get();
    }

    public static function getListCount(){
        return self::db()->count();
    }

    public static function add($data){
        if(!$data) return false;
        return self::db()->insert($data);
    }

    public static function update($type_id,$data){
        if(!$type_id || !$data) return false;
        return self::db()->where('type_id',$type_id)->update($data);
    }

    public static function delete($type_id){
        if(!$type_id) return false;
        return self::db()->where('type_id',$type_id)->delete();
    }

    public static function getAllTypeInfo(){
        return self::db()->lists('type_name','type_id');
    }
}