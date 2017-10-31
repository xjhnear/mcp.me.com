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

class DcActivity extends Model implements IModel{

    public static function getClassName(){
        return __CLASS__;
    }

    public static function getInfoByHashcode($hashcode){
        if(!$hashcode) return false;
        return self::db()->where('hashcode',$hashcode)->first();
    }

    public static function getInfoByCommand($command){
        if(!$command) return false;
        return self::db()->where('command',$command)->first();
    }

    public static function getValidCommands(){
        $query = self::db();
        $now = time();
        $query->where('start_time','<=',$now);
        $query->where('end_time','>=',$now);
        $query->where('is_open',1);
        return $query->lists('command');
    }

    public static function getInfo($activity_id=false,$name='',$start_time='',$end_time='',$command='',$page_index=1,$page_size=10){
        $query = self::db();
        $activity_id && $query->where('activity_id',$activity_id);
        $name && $query->where('name',$name);
        $start_time && $query->where('start_time','>=',$start_time);
        $end_time && $query->where('end_time','<=',$end_time);
        $command && $query->where('command',$command);
        $query->forPage($page_index,$page_size);
        $query->orderBy('add_time','desc');
        return $activity_id ? $query->first() : $query->get();
    }

    public static function getInfoCount($activity_id=false,$name='',$start_time='',$end_time='',$command=''){
        $query = self::db();
        $activity_id && $query->where('activity_id',$activity_id);
        $name && $query->where('name',$name);
        $start_time && $query->where('start_time','>=',$start_time);
        $end_time && $query->where('end_time','<=',$end_time);
        $command && $query->where('command',$command);
        return $query->count();
    }

    public static function getInfoByIds($ids=array()){
        if(!$ids) return false;
        return self::db()->whereIn('activity_id',$ids)->lists('name','activity_id');
    }

    public static function getInfoByLotids($lotids=array()){
        if(!$lotids) return false;
        return self::db()->whereIn('lottery_id',$lotids)->where('is_lotted',0)->first();
    }

    public static function add($data){
        if(!$data) return false;
        return self::db()->insert($data);
    }

    public static function update($actid,$data){
        if(!$actid || !$data) return false;
        return self::db()->where('activity_id',$actid)->update($data);
    }

    public static function delete($actid){
        if(!$actid) return false;
        $query = self::db();
        if(is_array($actid)){
            $query->whereIn('activity_id',$actid);
        }else{
            $query->where('activity_id',$actid);
        }
        return $query->delete();
    }
}