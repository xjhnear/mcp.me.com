<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/1/5
 * Time: 18:09
 */
namespace Youxiduo\Bbs\Model;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

class BbsGiftbag extends Model implements IModel{
    public static function getClassName(){
        return __CLASS__;
    }

    /**
     * 获取首页推荐礼包
     * @param int $is_show
     * @param int $limit
     * @return mixed
     */
    public static function getBbsGiftbag($is_show=1,$limit=6){
        $query = self::db();
        $query->where('is_show',$is_show);
        $query->take($limit);
        $query->orderBy('add_time','desc');
        return $query->get();
    }

    public static function getBbsGiftbagById($giftbag_id){
        if(!$giftbag_id) return false;
        return self::db()->where('bbs_giftbag_id',$giftbag_id)->first();
    }

    public static function getBbsGiftbagList($page=1,$limit=10){
        return self::db()->forPage($page,$limit)->orderBy('add_time','desc')->get();
    }

    public static function getBbsGiftbagCount(){
        return self::db()->count();
    }

    public static function insertBbsGiftbag($data){
        if(!$data) return false;
        return self::db()->insert($data);
    }

    public static function updateBbsGiftbag($giftbag_id,$data){
        if(!$giftbag_id) return false;
        return self::db()->where('bbs_giftbag_id',$giftbag_id)->update($data);
    }

    public static function deleteBbsGiftbag($giftbag_id){
        if(!$giftbag_id) return false;
        return self::db()->where('bbs_giftbag_id',$giftbag_id)->delete();
    }
}