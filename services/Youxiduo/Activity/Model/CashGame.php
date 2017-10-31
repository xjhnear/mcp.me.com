<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/5/26
 * Time: 15:07
 */
namespace Youxiduo\Activity\Model;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

class CashGame extends Model implements IModel
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    public static function save($data){
        if(!isset($data['id'])) return false;
        $id = $data['id'];
        unset($data['id']);
        foreach($data as &$v){
            if(empty($v)) unset($v);
        }
        if(!$data) return false;
        if($id){
            return self::db()->where('id',$id)->update($data);
        }else{
            return self::db()->insertGetId($data);
        }

    }

    /**
     * 列表
     */
    public static function getList($search,$pageIndex=1,$pageSize=10,$sort=array())
    {
        $out = array();
        $tb = self::db();
        if($search){
            foreach($search as $k=>$v){
                if(is_array($v)){
                    $tb->whereIn($k,$v);
                }else{
                    $tb->where($k,$v);
                }
            }
        }
        $out['totalCount'] = $tb->count();
        $tb->forPage($pageIndex,$pageSize);
        foreach($sort as $field=>$order){
            $tb->orderBy($field,$order);
        }
        $out['result'] = $tb->get();
        return $out;
    }

    //通过游戏ID获取游戏
    public static function getGamePassIDs(array $ids){
        if(!is_array($ids) || empty($ids)) return array();
        return self::db()->whereIn('gid', $ids)->get();
    }

    public static function getDetail($id){
        if(!$id) return array();
        return self::db()->where('id',$id)->first();
    }

    public static function getDel($id){
        if(!$id) return false;
        return self::db()->where('id',$id)->delete();
    }
}