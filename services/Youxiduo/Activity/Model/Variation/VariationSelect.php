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

class VariationSelect extends Model implements IModel
{
    public static function getClassName(){
        return __CLASS__;
    }

    public static function getInfo($act_id='',$uid='',$depot_id=''){
        if(!$act_id && !$uid && !$depot_id) return false;
        $query = self::db();
        $act_id && $query->where('activity_id',$act_id);
        $uid && $query->where('user_id',$uid);
        $depot_id && $query->where('depot_id',$depot_id);
        return $query->get();
    }

    public static function add($data){
        if(!$data) return false;
        return self::db()->insert($data);
    }

    public static function update($select_id,$data=array()){
        if(!$select_id || !$data) return false;
        return self::db()->where('select_id',$select_id)->update($data);
    }
}
