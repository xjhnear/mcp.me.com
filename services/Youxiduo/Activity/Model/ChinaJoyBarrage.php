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

class ChinaJoyBarrage extends Model implements IModel
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
    public static function getList($pagesize = 10,$page = 1 , $keyword='' , $make = true){
        $tb = self::db();
        $out = array();
        if($make){
            $out = $tb->orderBy(DB::Raw('rand()'))->limit($pagesize)->lists('content');
        }else{
            if($keyword){
                $tb->where('content','like',"%{$keyword}%");
            }
            $out['total'] = $tb->count();
            $out['result'] = $tb->orderBy('addtime','desc')->forPage($page,$pagesize)->get();
        }
        return $out;
    }

    public static function getDel($id){
        if(!$id) return false;
        return self::db()->where('id',$id)->delete();
    }
}