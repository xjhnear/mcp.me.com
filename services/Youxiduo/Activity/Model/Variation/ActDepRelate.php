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

class ActDepRelate extends Model implements IModel
{
    public static function getClassName(){
        return __CLASS__;
    }

    public static function add($data){
        if(!$data) return false;
        return self::db()->insert($data);
    }

    public static function update($relate_id,$data){
        if(!$relate_id || !$data) return false;
        return self::db()->where('activity_id',$relate_id)->update($data);
    }

    public static function delete($relate_id){
        if(!$relate_id) return false;
        return self::db()->where('activity_id',$relate_id)->delete();
    }

    public static function deleteByActivityId($act_id){
        if(!$act_id) return false;
        return self::db()->where('activity_id',$act_id)->delete();
    }

    public static function deleteByDepotId($depot_id){
        if(!$depot_id) return false;
        return self::db()->where('depot_id',$depot_id)->delete();
    }

    public static function getTargetList($type='',$activity_id='',$depot_id='',$belong=''){
        $query = self::db();
        $type && $query->where('type',$type);
        if($activity_id){
            if(is_array($activity_id)){
                $query->whereIn('activity_id',$activity_id);
            }else{
                $query->where('activity_id',$activity_id);
            }
        }

        if($depot_id){
            if(is_array($depot_id)){
                $query->whereIn('depot_id',$depot_id);
            }else{
                $query->where('depot_id',$depot_id);
            }
        }

        $belong && $query->where('belong',$belong);
        return $query->get();
    }
}
