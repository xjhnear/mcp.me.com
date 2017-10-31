<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/3/31
 * Time: 11:27
 */
namespace Youxiduo\Activity\Model\Variation;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

class VariationShow extends Model implements IModel
{
    public static function getClassName(){
        return __CLASS__;
    }

    public static function getInfo($show_id){
        if(!$show_id) return false;
        return self::db()->where('show_id',$show_id)->first();
    }

    public static function getList($page=1,$size=10){
        return self::db()->forPage($page,$size)->orderBy('sort','asc')->get();
    }

    public static function getListCount(){
        return self::db()->count();
    }

    public static function insert($data){
        if(!$data) return false;
        return self::db()->insert($data);
    }

    public static function update($show_id,$data){
        if(!$show_id || !$data) return flase;
        return self::db()->where('show_id',$show_id)->update($data);
    }

    public static function delete($show_id){
        if(!$show_id) return false;
        return self::db()->where('show_id',$show_id)->delete();
    }

    public static function getValidShowList(){
        return self::db()->where('is_show',1)->orderBy('sort','asc')->take(8)->get();
    }
}
