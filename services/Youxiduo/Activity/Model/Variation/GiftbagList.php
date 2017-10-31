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

class GiftbagList extends Model implements IModel
{
    public static function getClassName(){
        return __CLASS__;
    }

    public static function add($data){
        if(!$data) return false;
        return self::db()->insert($data);
    }

    public static function getForeList($depot_id,$page,$size){
        if(!$depot_id) return false;
        $query = self::db();
        return $query->where('depot_id',$depot_id)->where('is_send',1)->forPage($page,$size)->orderBy('list_id','desc')->get();
    }

    public static function getForeListCount($depot_id){
        if(!$depot_id) return 0;
        $query = self::db();
        return $query->where('depot_id',$depot_id)->where('is_send',1)->count();
    }

    public static function getList($depot_id,$page,$size,$uid=''){
        if(!$depot_id) return false;
        $query = self::db();
        $uid && $query->where('user_id',$uid);
        return $query->where('depot_id',$depot_id)->forPage($page,$size)->orderBy('list_id','desc')->get();
    }

    public static function getListCount($depot_id,$uid=''){
        if(!$depot_id) return 0;
        $query = self::db();
        $uid && $query->where('user_id',$uid);
        return $query->where('depot_id',$depot_id)->count();
    }

    public static function getInfo($list_id='',$depot_id=''){
        if(!$depot_id && !$depot_id) return false;
        $query = self::db();
        $list_id && $query->where('list_id',$list_id);
        $depot_id && $query->where('depot_id',$depot_id);
        return $list_id ? $query->first() : $query->get();
    }

    public static function update($list_id,$data){
        if(!$list_id || !$data) return false;
        return self::db()->where('list_id',$list_id)->update($data);
    }

    public static function importCardno($depot_id,$data){
        if(!$depot_id || !$data) return false;
        try{
            self::transaction(function()use($depot_id,$data){
                GiftbagList::add($data);
                GiftbagDepot::initCardNumber($depot_id);
            });
        }catch (\Exception $e){
            return false;
        }
        return true;
    }

    public static function getValidCardInfo($depot_id){
        if(!$depot_id) return false;
        return self::db()->where('depot_id',$depot_id)->where('is_get',0)->first();
    }

    public static function robUpdateCard($list_id,$data){
        if(!$list_id || !$data) return false;
		return self::db()->where('list_id',$list_id)->where('is_get',0)->update($data);
    }

    public static function getValidCardInfoByCardno($depot_id,$cardno){
        if(!$depot_id || !$cardno) return false;
        return self::db()->where('depot_id',$depot_id)->where('cardno',$cardno)->where('is_send',0)->first();
    }

    public static function getSharedCardRecord($depot_id,$user_id){
        if(!$depot_id || !$user_id) return false;
        return self::db()->where('depot_id',$depot_id)->where('user_id',$user_id)->where('is_get',1)->where('is_send',1)->first();
    }

    public static function getLastValidCard($depot_id){
        if(!$depot_id) return false;
        return self::db()->where('depot_id',$depot_id)->where('is_get',0)->whereNull('user_id')->first();
    }

    public static function updateOneRecord($depot_id,$user_id,$depot_info=array()){
        if(!$depot_id || !$user_id) return false;
        $list_id = null;
        try{
            $updata = array(
                'is_get' => 1,
                'gettime' => time(),
                'user_id' => $user_id
            );
            $db = self::db();
            self::transaction(function()use($depot_id,$updata,$depot_info,$db,&$list_id){
                $db->where('depot_id',$depot_id)->where('is_get',0)->whereNull('user_id')->take(1)->update($updata);
                $cardinfo = GiftbagList::getValidSharedCardRecord($depot_id,$depot_info['uid']);
                $list_id = $cardinfo['list_id'];

                $m_data = array(
                    'gift_id' => $depot_info['m_giftbag_id'],
                    'game_id' => $depot_info['gid'],
                    'uid' => $depot_info['uid'],
                    'card_no' => $cardinfo['cardno'],
                    'addtime' => time()
                );
                GiftbagAccount::addMyGiftbag($m_data);
            });
        }catch (\Exception $e){
            return false;
        }
        return $list_id;
    }

    public static function getValidSharedCardRecord($depot_id,$user_id){
        if(!$depot_id || !$user_id) return false;
        return self::db()->where('depot_id',$depot_id)->where('user_id',$user_id)->where('is_get',1)->first();
    }

    public static function getSendedCardNum($depot_id){
        if(!$depot_id) return 0;
        return self::db()->where('depot_id',$depot_id)->where('is_send',1)->count();
    }

    public static function getUnusedCard($depot_id,$number,$valid=0){
        if(!$depot_id || !$number) return false;
        $query = self::db();
        $valid && $query->where('is_get',0);
        return $query->where('depot_id',$depot_id)->where('is_send',0)->orderBy('list_id','asc')->take($number)->get();
    }

    public static function deleteUnusedCard($depot_id,$list_ids){
        if(!$depot_id || !$list_ids) return false;
        $query = self::db();
        try{
            self::transaction(function()use($query,$depot_id,$list_ids){
                $query->where('depot_id',$depot_id)->whereIn('list_id',$list_ids)->where('is_send',0)->delete();
                GiftbagDepot::initCardNumber($depot_id);
            });
        }catch (\Exception $e){
            return false;
        }
        return true;
    }

    public static function delete($depot_id){
        if(!$depot_id) return false;
        $query = self::db();
        try{
            self::transaction(function()use($query,$depot_id){
                GiftbagDepot::delete($depot_id);
                $query->where('depot_id',$depot_id)->delete();
                ActDepRelate::deleteByDepotId($depot_id);
            });
        }catch (\Exception $e){
            return false;
        }
        return true;
    }

    public static function getStatisticsCount($depot='',$start='',$end=''){
        $query = self::db();
        if($depot){
            is_array($depot) ? $query->whereIn('depot_id',$depot) : $query->where('depot_id',$depot);
        }
        $start && $query->where('updatetime','>=',$start);
        $end && $query->where('updatetime','<',$end);
        $query->whereNotNull('user_id');
        return $query->count();
    }
}
