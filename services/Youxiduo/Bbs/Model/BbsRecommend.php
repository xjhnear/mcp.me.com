<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/1/5
 * Time: 17:01
 */
namespace Youxiduo\Bbs\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

class BbsRecommend extends Model implements IModel{
    public static function getClassName(){
        return __CLASS__;
    }

    /**
     * 获取推荐帖子
     * @param int $is_show
     * @param int $limit
     * @param string $sort  sort:sort time:add_time
     * @return mixed
     */
    public static function getBbsRecommend($is_show=1,$limit=7,$sort='sort'){
        $query = self::db();
        $query->where('is_show',$is_show);
        $query->take($limit);
        if($sort == 'sort') $query->orderBy('sort','asc');
        if($sort == 'time') $query->orderBy('add_time','desc');
        return $query->get();

    }

    public static function getBbsRecommendById($recommend_id){
        if(!$recommend_id) return false;
        return self::db()->where('bbs_recommend_id',$recommend_id)->first();
    }

    public static function getBbsRecommendList($page=1,$limit=10){
        return self::db()->forPage($page,$limit)->orderBy('sort','asc')->get();
    }

    public static function getBbsRecommendCount(){
        return self::db()->count();
    }

    public static function insertBbsRecommend($data){
        if(!$data) return false;
        return self::db()->insert($data);
    }

    public static function updateBbsRecommend($recommend_id,$data){
        if(!$recommend_id || !$data) return false;
        return self::db()->where('bbs_recommend_id',$recommend_id)->update($data);
    }

    public static function deleteBbsRecommend($recommend_id){
        if(!$recommend_id) return false;
        return self::db()->where('bbs_recommend_id',$recommend_id)->delete();
    }
}