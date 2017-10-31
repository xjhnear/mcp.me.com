<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/3/27
 * Time: 15:43
 */
namespace Youxiduo\Activity\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
use Illuminate\Support\Facades\DB;

class DcPrize extends Model implements IModel{

    public static function getClassName(){
        return __CLASS__;
    }

    public static function add($data){
        if(!$data) return false;
        return self::db()->insert($data);
    }

    public static function getInfo($prize_id='',$lottery_id='',$page_index=1,$page_size=10){
        $query = self::db();
        $prize_id && $query->where('prize_id',$prize_id);
        $lottery_id && $query->where('lottery_id',$lottery_id);
        $query->forPage($page_index,$page_size);
        $query->orderBy('probab','asc');
        $query->orderBy('lottery_id');
        return $prize_id ? $query->first() : $query->get();
    }

    public static function update($prize_id,$data){
        if(!$prize_id || !$data) return false;
        return self::db()->where('prize_id',$prize_id)->update($data);
    }

    public static function delete($prize_id){
        if(!$prize_id) return false;
        return self::db()->where('prize_id',$prize_id)->delete();
    }

    public static function getInfoCount($prize_id='',$lottery_id=''){
        $query = self::db();
        $prize_id && $query->where('prize_id',$prize_id);
        $lottery_id && $query->where('lottery_id',$lottery_id);
        return $query->count();
    }

    public static function getInfoByIds($ids=array()){
        if(!$ids) return false;
        return self::db()->whereIn('lottery_id',$ids)->orderBy('probab','asc')->orderBy('number','asc')->get();
    }
}