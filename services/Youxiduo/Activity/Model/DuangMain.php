<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/3/11
 * Time: 14:06
 */
namespace Youxiduo\Activity\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
use Illuminate\Support\Facades\DB;

class DuangMain extends Model implements IModel
{
	public static function getClassName()
	{
		return __CLASS__;
	}

	public static function addInfo($data){
		if(!$data) return false;
		return self::db()->insert($data);
	}

	public static function getInfo($phone='',$uid='',$giftbag_id='',$destroy=false){
		$query = self::db();
		if($phone) $query->where('phone',$phone);
		if($uid) $query->where('share_from',$uid);
		if($giftbag_id) $query->where('giftbag_id',$giftbag_id);
		if($destroy !== false) $query->where('destroy',$destroy);
		if($phone && $destroy !== false){
			return $query->first();
		}else{
			return $query->get();
		}
	}

	public static function updateInfo($id,$data){
		if(!$id || !$data) return false;
		$query = self::db();
		$query->where('id',$id);
		return $query->update($data);
	}

    public static function robUpdateInfo($id,$data){
		if(!$id || !$data) return false;
		$query = self::db();
		$query->where('id',$id);
        $query->where('destroy',0);
		return $query->update($data);
	}

	/**
	 * 获取过期未领取且有效的记录
	 * @param $giftbag_id
	 * @param $expire_time
	 * @return mixed
	 */
	public static function getExpiredInfo($giftbag_id,$expire_time){
		$query = self::db();
		$query->where('giftbag_id',$giftbag_id);
		$query->where('received',0);
		$query->where('destroy',0);
		$query->where('expiretime','<',$expire_time);
		$query->orderBy('addtime','asc');
		return $query->first();
	}

	public static function getValidShowList($giftbag_id,$from_uid){
		$query = self::db();
		$query->select('giftbag_id','phone','from_uid','received','addtime');
		$query->where('received',1);
		$query->where('giftbag_id',$giftbag_id);
		$query->where('from_uid',$from_uid);
		$query->where('destroy',0);
		$query->orWhere(function($qu) use($giftbag_id,$from_uid){
            $qu->where('giftbag_id',$giftbag_id);
            $qu->where('from_uid',$from_uid);
			$qu->where('expiretime','>',time());
		});
		$query->orderBy('addtime','desc');
		return $query->get();
	}

	public static function getRobTimesByIp($ip,$expire_second){
		if(!$ip) return 0;
		$query = self::db();
		$query->where('ip_address',$ip);
		$query->where('addtime','>',time()-$expire_second);
		return $query->count();
	}

	public static function getValidJoinTimesByFromUid($giftbag_id,$from_uid){
		if(!$from_uid) return false;
		$query = self::db();
		$query->where('from_uid',$from_uid);
		$query->where('received',1);
		$query->where('giftbag_id',$giftbag_id);
		$query->orWhere(function($qu) use($giftbag_id,$from_uid){
            $qu->where('giftbag_id',$giftbag_id);
            $qu->where('from_uid',$from_uid);
			$qu->where('expiretime','>',time());
		});
		return $query->count();
	}

	/**
	 * 获取有效的领取人列表
	 * @return mixed
     */
	public static function getAutoSendValidInfo($giftbag_ids){
        if(!$giftbag_ids) return false;
		$query = self::db();
        $query->whereIn('giftbag_id',$giftbag_ids);
		$query->where('received',0);
		$query->where('send_msg',0);
		return $query->get();
	}

	public static function getAutoSendSharemanValidInfo($giftbag_ids){
        if(!$giftbag_ids) return false;
		$query = self::db();
		$query->select(DB::raw('id,giftbag_id,from_uid,COUNT(*) AS num,share_send_msg'));
		$query->where('received',1);
        $query->whereIn('giftbag_id',$giftbag_ids);
		$query->groupBy('from_uid','giftbag_id');
		return $query->get();
	}

	public static function getInfoByCards($cards){
		if(!$cards || !is_array($cards)) return false;
		return self::db()->where('destroy','!=',1)->whereIn('giftcard',$cards)->get();
	}

    public static function getValidInfoByPhones($phones=array()){
        if(!$phones) return false;
        return self::db()->whereIn('phone',$phones)->where('received',1)->lists('from_uid','phone');
    }
}
