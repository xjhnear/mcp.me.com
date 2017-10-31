<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/3/31
 * Time: 11:27
 */
namespace Youxiduo\Activity\Model\Variation;

use Illuminate\Support\Facades\DB;
use Youxiduo\Android\Model\GiftbagAccount;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

class VariationMain extends Model implements IModel
{
    public static function getClassName(){
        return __CLASS__;
    }

    public static function add($data){
        if(!$data) return false;
        return self::db()->insert($data);
    }

    public static function getList($page,$size){
        $query = self::db();
        return $query->forPage($page,$size)->get();
    }

    public static function getListCount(){
        $query = self::db();
        return $query->count();
    }

    public static function getInfo($main_id='',$activity_id=''){
        if(!$main_id && !$activity_id) return false;
        $query = self::db();
        $main_id && $query->where('main_id',$main_id);
        $activity_id && $query->where('activity_id',$activity_id);
        return $main_id ? $query->first() : $query->get();
    }

    public static function update($main_id,$data,$depot_info=array()){
        if(!$main_id || !$data) return false;
        $db = self::db();
        try{
            self::transaction(function()use($main_id,$data,$db,$depot_info){
                if($depot_info){
                    $m_data = array(
                        'gift_id' => $depot_info['m_giftbag_id'],
                        'game_id' => $depot_info['gid'],
                        'uid' => $depot_info['uid'],
                        'card_no' => $depot_info['card_no'],
                        'addtime' => time()
                    );
                    GiftbagAccount::addMyGiftbag($m_data);
                }
                $db->where('main_id',$main_id)->update($data);
            });
        }catch (\Exception $e){
            return false;
        }
        return true;
    }

    public static function delete($main_id){
        if(!$main_id) return false;
        return self::db()->where('main_id',$main_id)->delete();
    }

    public static function getRobTimesByIp($ip,$expire_second){
		if(!$ip) return 0;
		$query = self::db();
		$query->where('ip_address',$ip);
		$query->where('addtime','>',time()-$expire_second);
		return $query->count();
	}

    public static function getJoinedRecord($phone,$activity_id){
        $query = self::db();
		$query->where('phone',$phone);
		$query->where('activity_id',$activity_id);
		$query->where('destroy',0);
        return $query->first();
    }

    public static function getExpiredInfo($activity_id,$depot_id,$expire_time){
		$query = self::db();
		$query->where('activity_id',$activity_id);
        $query->where('depot_id',$depot_id);
		$query->where('received',0);
		$query->where('destroy',0);
		$query->where('expiretime','<',$expire_time);
		$query->orderBy('addtime','asc');
		return $query->first();
	}

    public static function robUpdateInfo($main_id,$data){
		if(!$main_id || !$data) return false;
		$query = self::db();
		$query->where('main_id',$main_id);
        $query->where('destroy',0);
		return $query->update($data);
	}

    public static function getValidAutoSendList($activity_ids){
        if(!$activity_ids) return false;
        $yestorday = time()-86400;
        $query = self::db();
        $query->whereIn('activity_id',$activity_ids);
        $query->where('received',0);
        $query->where('send_msg',0);
        $query->where('expiretime','>',$yestorday);
        return $query->get();
    }

    public static function getAutoSendSharemanValidInfo($activity_ids){
        if(!$activity_ids) return false;
		$query = self::db();
		$query->select(DB::raw('main_id,activity_id,from_uid,COUNT(*) AS num,send_msg,share_send_msg'));
        $query->whereIn('activity_id',$activity_ids);
        $query->where(function($q){
            $q->where('received',1);
            $q->orWhere('send_msg',1);
        });
		$query->groupBy('from_uid','activity_id');
		return $query->get();
	}

    public static function getSuccessedInfo($activity_id){
        if(!$activity_id) return false;
        return self::db()->where('activity_id',$activity_id)->where('received',1)->orWhere(function($query)use($activity_id){
            $query->where('activity_id',$activity_id)->where('destroy',0)->where('expiretime','>',time());
        })->get();
    }

    public static function getGiftbagDepotJoinListCount($depot_id){
        if(!$depot_id) return false;
        return self::db()->where('depot_id',$depot_id)->where('received',1)->orWhere(function($query)use($depot_id){
            $query->where('depot_id',$depot_id)->where('destroy',0)->where('expiretime','>',time());
        })->count();
    }

    public static function getGiftbagDepotJoinList($depot_id,$page=1,$size=10){
        if(!$depot_id) return false;
        return self::db()->where('depot_id',$depot_id)->where('received',1)->orWhere(function($query)use($depot_id){
            $query->where('depot_id',$depot_id)->where('destroy',0)->where('expiretime','>',time());
        })->forPage($page,$size)->orderBy('addtime','desc')->get();
    }

    public static function getShareRecordList($activity_id,$page,$size,$from_uid=''){
        if(!$activity_id) return false;
        $query = self::db();
        $from_uid && $query->where('from_uid',$from_uid);
        return $query->where('activity_id',$activity_id)->forPage($page,$size)->orderBy('from_uid','desc')->orderBy('received','desc')->orderBy('addtime','desc')->get();
    }

    public static function getShareRecordListCount($activity_id,$from_uid=''){
        if(!$activity_id) return false;
        $query = self::db();
        $from_uid && $query->where('from_uid',$from_uid);
        return $query->where('activity_id',$activity_id)->count();
    }
}
