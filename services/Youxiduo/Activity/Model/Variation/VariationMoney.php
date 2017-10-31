<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/3/31
 * Time: 11:27
 */
namespace Youxiduo\Activity\Model\Variation;
use Illuminate\Support\Facades\DB;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

class VariationMoney extends Model implements IModel
{
    public static function getClassName(){
        return __CLASS__;
    }

    public static function add($data){
        if(!$data) return false;
        return self::db()->insertGetId($data);
    }

    public static function getList($act_id,$page=1,$size=10){
        if(!$act_id) return false;
        return self::db()->where('activity_id',$act_id)->forPage($page,$size)->get();
    }

    public static function getListCount($act_id){
        if(!$act_id) return 0;
        return self::db()->where('activity_id',$act_id)->count();
    }

    public static function getInfo($act_id,$uid,$type,$money=''){
        if(!$act_id || !$uid || !$type) return false;
        $query = self::db();
        $money && $query->where('money',$money);
        return $query->where('activity_id',$act_id)->where('user_id',$uid)->where('type',$type)->first();
    }

    public static function delete($money_id){
        if(!$money_id) return false;
        return self::db()->where('money_id',$money_id)->delete();
    }

    public static function getMoneyList($activity_id,$type='',$page=1,$size=10){
        if(!$activity_id) return false;
        $query = self::db();
        $query->where('activity_id',$activity_id);
        $type && $query->where('type',$type);
        return $query->orderBy('addtime','desc')->forPage($page,$size)->get();
    }

    public static function getMoneyListCount($activity_id,$type=''){
        if(!$activity_id) return false;
        $query = self::db();
        $query->where('activity_id',$activity_id);
        $type && $query->where('type',$type);
        return $query->count();
    }
}
