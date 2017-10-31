<?php
/**
 * @package Youxiduo
 * @category Android 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\Android\Model;
use Illuminate\Support\Facades\Cache;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

/**
 * 活动模型类
 */
final class TaskBlacklist extends Model implements IModel
{
    public static function getClassName()
    {
        return __CLASS__;
    }


    public static function save($data)
    {
        if (isset($data['id']) && $data['id']) {
            $id = $data['id'];
            unset($data['id']);
            $res = self::db()->where('id', '=', $id)->update($data);
            if($res){
                return $res;
            }else{
                return false;
            }

        } else {
            unset($data['id']);
            $data['createtime'] = date('Y-m-d');

            return self::db()->insertGetId($data);
        }
    }

    public static function find_by_key($type,$key){

        return $res = self::db()->where($type, '=', $key)->orderBy('id', 'desc')->first();

    }

    /*
     * 获取黑名单列表
     */
    public static function find_blacklist($search,$pageIndex=1,$pageSize=10)
    {
        if(isset($search['uid'])){
            $db = self::db()->where('uid', $search['uid']);
        }else{
            $db = self::db();
        }
        return  $db->forPage($pageIndex,$pageSize)->orderBy('id', 'desc')->get();
    }
    /*
     * 获取黑名单列表
     */
    public static function find_blacklist_count($search,$pageIndex=1,$pageSize=10)
    {
        if(isset($search['uid'])){
            $db = self::db()->where('uid', $search['uid']);
        }else{
            $db = self::db();
        }
        return  $db->count();
    }

    /*
     * 根据id 删除单数据
     */
    public static function del_blacklist($id)
    {
        $res = self::db()->where('id','=',$id)->delete();
        return $res;
    }
}