<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/3/31
 * Time: 11:27
 */
namespace Youxiduo\Activity\Model\Variation;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

class VariationActivity extends Model implements IModel
{
    public static function getClassName(){
        return __CLASS__;
    }

    public static function add($data){
        if(!$data) return false;
        return self::db()->insert($data);
    }

    public static function getList($page,$size,$title=''){
        $query = self::db();
        if($title) $query->where('title','like','%'.$title.'%');
        return $query->forPage($page,$size)->orderBy('activity_id','desc')->get();
    }

    public static function getListCount($title=''){
        $query = self::db();
        if($title) $query->where('title','like','%'.$title.'%');
        return $query->count();
    }

    public static function getInfo($activity_id='',$hashcode=''){
        if(!$activity_id && !$hashcode) return false;
        $query = self::db();
        $activity_id && $query->where('activity_id',$activity_id);
        $hashcode && $query->where('hashcode',$hashcode);
        return $query->first();
    }

    public static function getInfoByArticleid($artid){
        if(!$artid) return false;
        return self::db()->where('article_id',$artid)->first();
    }

    public static function update($activity_id,$data){
        if(!$activity_id || !$data) return false;
        return self::db()->where('activity_id',$activity_id)->update($data);
    }

    public static function addActivityAndRelate($adata,$director=array(),$sharer=array()){
        if(!$adata) return false;
        $query = self::db();
        try{
            self::transaction(function()use($query,$adata,$director,$sharer){
                $aid = $query->insertGetId($adata);
                $rdata = array();
                if($director){
                    foreach($director as $row){
                        $rdata[] = array('type'=>'variation','activity_id'=>$aid,'depot_id'=>$row,'belong'=>'newer');
                    }
                }
                if($sharer){
                    foreach($sharer as $row){
                        $rdata[] = array('type'=>'variation','activity_id'=>$aid,'depot_id'=>$row,'belong'=>'sharer');
                    }
                }
                ActDepRelate::add($rdata);
            });
        }catch (\Exception $e){
            return false;
        }
        return true;
    }

    public static function updateActivityAndRelate($act_id,$adata,$director=array(),$sharer=array()){
        if(!$act_id || !$adata) return false;
        $query = self::db();
        try{
            self::transaction(function()use($query,$act_id,$adata,$director,$sharer){
                $query->where('activity_id',$act_id)->update($adata);
                $rdata = array();
                if($director){
                    foreach($director as $row){
                        $rdata[] = array('type'=>'variation','activity_id'=>$act_id,'depot_id'=>$row,'belong'=>'newer');
                    }
                }
                if($sharer){
                    foreach($sharer as $row){
                        $rdata[] = array('type'=>'variation','activity_id'=>$act_id,'depot_id'=>$row,'belong'=>'sharer');
                    }
                }
                ActDepRelate::deleteByActivityId($act_id);
                ActDepRelate::add($rdata);
            });
        }catch (\Exception $e){
            return false;
        }
        return true;
    }

    public static function deleteActAndRelate($act_id){
        if(!$act_id) return false;
        $query = self::db();
        try{
            self::transaction(function()use($query,$act_id){
                $query->where('activity_id',$act_id)->delete();
                ActDepRelate::deleteByActivityId($act_id);
            });
        }catch (\Exception $e){
            return false;
        }
        return true;
    }

    public static function getIsShowAllInfo(){
        return self::db()->where('is_show',1)->get();
    }


}
