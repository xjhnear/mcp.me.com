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
final class Plat360Log extends Model implements IModel
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    //执行添加日志
    public static function addLog($data){
        if(!$data) return false;
        return self::db()->insertGetId($data);
    }

    //查询最后一条日志
    public  static  function getEndLog($params,$feilds = array()){
        $tb = self::db();
        if(is_array($params)){
            foreach ($params as $k => $v) {
                $tb->where($k,$v);
            }
        }
        if(!empty($feilds)) $tb->select($feilds);
       $result = $tb->orderBy('addtime','desc')->first();
        //print_r(self::getQueryLog());exit;
        return $result;
    }

    public static function upLog($id){
        $data = array('result'=>1);
        return self::db()->where('id',$id)->update($data);
    }
}