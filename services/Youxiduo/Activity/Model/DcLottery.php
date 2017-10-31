<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/3/27
 * Time: 15:40
 */
namespace Youxiduo\Activity\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

class DcLottery extends Model implements IModel{

    public static function getClassName(){
        return __CLASS__;
    }

    public static function getInfo($lottery_id=false,$title='',$lot_type=false,$starttime='',$endtime='',$page_index=1,$page_size=10){
        $query = self::db();
        $lottery_id && $query->where('lottery_id',$lottery_id);
        $title && $query->where('title',$title);
        $lot_type && $query->where('lot_type',$lot_type);
        $starttime && $query->where('start_time','>=',$starttime);
        $endtime && $query->where('end_time','<=',$endtime);
        $query->forPage($page_index,$page_size);
        $query->orderBy('create_time','desc');
        return $lottery_id ? $query->first() : $query->get();
    }

    public static function getInfoCount($lottery_id=false,$title='',$lot_type=false,$starttime='',$endtime=''){
        $query = self::db();
        $lottery_id && $query->where('lottery_id',$lottery_id);
        $title && $query->where('title',$title);
        $lot_type && $query->where('lot_type',$lot_type);
        $starttime && $query->where('start_time','>=',$starttime);
        $endtime && $query->where('end_time','<=',$endtime);
        return $query->count();
    }

    public static function getInfoByIds($ids=array()){
        if(!$ids) return false;
        return self::db()->whereIn('lottery_id',$ids)->lists('title','lottery_id');
    }

    public static function getAllInfoByIds($ids=array()){
        if(!$ids) return false;
        return self::db()->whereIn('lottery_id',$ids)->get();
    }

    public static function add($data){
        if(!$data) return false;
        return self::db()->insert($data);
    }

    public static function update($lottery_id,$data){
        if(!$lottery_id || !$data) return false;
        return self::db()->where('lottery_id',$lottery_id)->update($data);
    }

    public static function delete($ids=''){
        if(!$ids) return false;
        $query = self::db();
        if(is_array($ids)){
            $query->whereIn('lottery_id',$ids);
        }else{
            $query->where('lottery_id',$ids);
        }
        return $query->delete();
    }

    public static function getAutoLotInfo(){
        $query = self::db();
        $query->where('lot_type',2);
        $query->where('valid',1);
        $query->where('prize_way',1);
        $query->where('send_time','<',time());
        return $query->get();
    }
}