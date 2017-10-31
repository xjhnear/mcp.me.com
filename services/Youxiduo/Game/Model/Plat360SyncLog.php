<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/4/1
 * Time: 14:22
 */

namespace Youxiduo\Game\Model;

use Illuminate\Support\Facades\DB;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
/**
 * 模型类
 */
final class Plat360SyncLog extends Model implements IModel
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    //执行添加日志
    public static function addLog($data){
        if(!$data) return false;
        if(!isset($data['addtime'])) $data['addtime'] = time();
        return self::db()->insertGetId($data);
    }

    /**
     * 获取同步地址日志
     * @param string $make
     * @param array $whereAddtime
     * @return mixed
     */
    public static function getlist($make = '', $whereAddtime = array()){
        $tb = self::db();
        if($whereAddtime){
            $tb = $tb->whereBetween('addtime',$whereAddtime);
        }
        if($make == 'wid'){
            $tb = $tb->where('plat',1)->where($make,'<>',0);
        }elseif($make == 'mid'){
            $tb = $tb->where('plat',2)->where($make,'<>',0);
        }
        return $tb->where('destroy',0)->get();
        //return $tb->where('destroy',0)->get();
    }

    public static function getLists($page = 1 , $pagesize = 10 , $feilds = array()){
        $tb = self::db();
        if(!empty($feilds)){
            $tb->select(self::raw($feilds));
        }
        $out['total'] = $tb->count();
        $out['result'] = $tb->forPage($page,$pagesize)->orderBy('id','desc')->get();
        return $out;
    }

    //修改
    public static function save($data){
        if(!$data || empty($data['id'])) return false;
        $id = $data['id'];
        unset($data['id']);
        if(!isset($data['destime'])) $data['destime'] = time();
        return self::db()->where('id',$id)->update($data);
    }

    //单个详情
    public static function getDetail($id){
        if(!$id) return false;
        return self::db()->where('id',$id)->first();
    }

}