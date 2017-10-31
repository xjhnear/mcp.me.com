<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2014/12/17
 * Time: 18:14
 */
namespace Youxiduo\Bbs\Model;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

class BbsHome extends Model implements Imodel{
    public static function getClassName(){
        return __CLASS__;
    }

    public static function getBbsListInfo($is_show=1, $limit=16){
        return self::db()->where('show',$is_show)->take($limit)->orderBy('type','asc')->orderBy('sort','asc')->get();
    }

    public static function getInfo($fid='',$gid=0,$home_id=0){
        if(!$fid && !$gid && !$home_id) return false;
        $query = self::db();
        if($fid) $query->where('fid',$fid);
        if($gid) $query->where('gid',$gid);
        if($home_id) $query->where('bbs_home_id',$home_id);
        return $query->first();
    }

    public static function addInfo($data){
        return self::db()->insert($data);
    }

    public static function updateInfo($home_id,$data){
        if(!$home_id || !$data) return false;
        return self::db()->where('bbs_home_id',$home_id)->update($data);
    }

    public static function deleteInfo($hid){
        if(!$hid) return false;
        if(is_array($hid)){
            return self::db()->whereIn('bbs_home_id',$hid)->delete();
        }else{
            return self::db()->where('bbs_home_id',$hid)->delete();
        }
    }

    public static function getBbsInfo($page=1,$limit=10){
        return self::db()->forPage($page,$limit)->orderBy('type')->orderBy('sort','desc')->get();
    }

    public static function getBbsInfoCount(){
        return self::db()->count();
    }
}