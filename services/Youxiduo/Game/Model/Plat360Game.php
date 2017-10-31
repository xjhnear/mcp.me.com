<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/4/1
 * Time: 14:22
 */

namespace Youxiduo\Game\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
/**
 * 模型类
 */
final class Plat360Game extends Model implements IModel
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    //执行添加
    public static function addGame(array $data){
        if(!$data) return false;
        return self::db()->insert($data);
    }
    //获取360平台下的所有游戏


    //修改状态
    public static function upGame(array $data){
        if(!$data || empty($data['id'])) return false;
        $id = $data['id'];
        unset($data['id']);
        return self::db()->where('id', $id)->update($data);
    }

    //查询游戏
    public  static  function getMeGame(array $params){
       return self::db()->whereIn('id',$params)->get();
    }

    /**
     * 获取单个游戏
     * @param $id
     * @param array $feilds
     * @return mixed
     */
    public static function getGame($id,$feilds = array('id','isSync','rDownloadUrl','name')){
        return self::db()->select($feilds)->where('id',$id)->first();
    }

    public static function getNameGame($gname){
        if(!$gname) return false;
        return self::db()->where('name',$gname)->where('isSync','!=',0)->get();
    }


    /**
     * 查找已经导入的游戏按更新时间按最大或者按最小排序
     * @param string $sort
     * @return mixed
     */

    public static function getFirstGame($sort = 'desc'){
        return self::db()->orderBy('updateTime',$sort)->first();
    }

    /**
     * 获取总条数
     * @return mixed
     */
    public static function getTotal(){
        return self::db()->count();
    }


}