<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/1/5
 * Time: 16:06
 */
namespace Youxiduo\Bbs\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

class BbsBanner extends Model implements IModel{
    public static function getClassName(){
        return __CLASS__;
    }

    /**
     * 获取首页展示banner
     * @param int $is_show
     * @param int $limit
     * @param string $sort_type sort:sort time:add_time
     * @return mixed
     */
    public static function getBbsBannerInfo($is_show=1,$limit=4,$sort_type='sort'){
        $query = self::db();
        $query->where('is_show',$is_show);
        $query->take($limit);
        if($sort_type == 'sort') $query->orderBy('sort','asc');
        if($sort_type == 'time') $query->orderBy('add_time','desc');
        return $query->get();
    }

    public static function getBbsBanner($page=1,$limit=10){
        return self::db()->forPage($page,$limit)->orderBy('add_time','desc')->get();
    }

    public static function getBbsBannerCount(){
        return self::db()->count();
    }

    public static function insertBannerCount($data){
        if(!$data) return false;
        return self::db()->insert($data);
    }

    public static function deleteBbsBanner($banner_id){
        if(!$banner_id) return false;
        return self::db()->where('bbs_banner_id',$banner_id)->delete();
    }

    public static function getBannerById($banner_id){
        if(!$banner_id) return false;
        return self::db()->where('bbs_banner_id',$banner_id)->first();
    }

    public static function updateBbaBanner($banner_id,$data){
        if(!$banner_id || !$data) return false;
        return self::db()->where('bbs_banner_id',$banner_id)->update($data);
    }
}