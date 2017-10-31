<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/4/1
 * Time: 15:03
 */
namespace Youxiduo\Activity\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
use Illuminate\Support\Facades\DB;

class DcJoin extends Model implements IModel{

    public static function getClassName()
    {
        return __CLASS__;
    }

    public static function getInfoById($join_id){
        if(!$join_id) return false;
        return self::db()->where('join_id',$join_id)->first();
    }

    public static function getInfo($user_id='',$activity_id='',$if_win=0,$page=1,$limit=10){
        if(!$user_id && !$activity_id) return false;
        $query = self::db();
        $user_id && $query->where('user_id',$user_id);
        $activity_id && $query->where('activity_id',$activity_id);
        $if_win && $query->where('if_win',1);
        return $query->forPage($page,$limit)->orderBy('if_win','desc')->orderBy('add_time','desc')->get();
    }

    public static function getWinInfo($user_id='',$activity_id='',$if_win=false,$msg_send=false){
        if(!$user_id && !$activity_id) return false;
        $query = self::db();
        $user_id && $query->where('user_id',$user_id);
        $activity_id && $query->where('activity_id',$activity_id);
        $if_win !== false && $query->where('if_win',$if_win);
        $msg_send === false && $query->where('msg_send',0);
        return $query->orderBy('if_win','desc')->orderBy('add_time','desc')->get();
    }

    public static function getInfoCount($user_id='',$activity_id='',$if_win=0){
        if(!$user_id && !$activity_id) return 0;
        $query = self::db();
        $user_id && $query->where('user_id',$user_id);
        $activity_id && $query->where('activity_id',$activity_id);
        $if_win && $query->where('if_win',1);
        return $query->count();
    }

    public static function getWinPrizeInfo($activity_id,$prize_ids){
        if(!$activity_id || !$prize_ids) return false;
        $query = self::db();
        $query->select(DB::raw('prize_id,count(*) as count'));
        $query->where('activity_id',$activity_id);
        $query->whereIn('prize_id',$prize_ids);
        $query->groupBy('prize_id');
        return $query->lists('count','prize_id');
    }

    public static function add($data){
        if(!$data) return false;
        return self::db()->insert($data);
    }

    public static function getRandWinner($actid,$num){
        if(!$actid || !$num) return false;
        $query = self::db();
        $query->where('activity_id',$actid);
        $query->orderBy(DB::raw('rand()'));
        $query->take($num);
        return $query->get();
    }

    public static function update($join_id,$data){
        if(!$join_id || !$data) return false;
        return self::db()->where('join_id',$join_id)->update($data);
    }

    public static function updateByActAndUser($uids=array(),$actid,$data){
        if(!$uids || !$actid) return false;
        return self::db()->whereIn('user_id',$uids)->where('activity_id',$actid)->where('if_win',1)->update($data);
    }

    public static function getLastValidJoinInfo($uid){
        if(!$uid) return false;
        $query = self::db();
        $query->where('user_id',$uid);
        $query->where('if_win',1);
        $query->where('msg_send',1);
        $query->whereNull('sub_info');
        $query->orderBy('add_time','desc');
        return $query->first();
    }
}