<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/7/1
 * Time: 15:52
 */
namespace Youxiduo\Activity\Model;
use Illuminate\Support\Facades\DB;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

class ChinaJoyGuide extends Model implements IModel
{
    public static function getClassName()
    {
        return __CLASS__;
    }
    //修改状态
    public static function save(array $data){
        if(!$data) return false;
        if(empty($data['id'])){
            return self::db()->insertGetId($data);
        }else{
            $id = $data['id'];
            unset($data['id']);
            if(empty($data)) return false;
            return self::db()->where('id',$id)->update($data);
        }

    }
    public static function getList($pagesize = 10,$page = 1 , $keyword=''){
        $tb = self::db();
        if($keyword){
            $tb = $tb->where('title','like',"%{$keyword}%");
        }
        $out['total'] = $tb->count();
        $tb = $tb->orderBy('addtime','asc');
        $pagesize != 0 && $tb = $tb->forPage($page,$pagesize);
        $out['result'] = $tb->get();
        return $out;
    }
    public static function getDetail($id){
        if(!$id) return false;
        return self::db()->where('id',$id)->first();
    }

    public static function getDel($id){
        if(!$id) return false;
        return self::db()->where('id',$id)->delete();
    }
}