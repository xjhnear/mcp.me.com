<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/4/8
 * Time: 10:43
 */

namespace Youxiduo\Game\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
/**
 * 模型类
 */
final class Plat360Sync extends Model implements IModel
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    /**
     * 查询游戏同步状态
     * @param array $where
     * @return mixed
     */
    public static function getState($where = array(),$make = true){
        $tb = self::db();
        $i = 1;
        foreach($where as $k => $v){
            if(!$make && $i!=1){
                $tb = $tb->orWhere($k,$v);
            }else{
                $tb = $tb->where($k,$v);
            }
            $i++;
        }
        return $tb->first();
    }


    public static function getWid($field = 'wid'){
        if(!$field) return array();
        return self::db()->lists($field);
    }

    /**
     * 获取所有mid 为空或者不为空的记录
     * @param int $mid
     * @return mixed
     */
    public static function getMid($mid = 0){
        $tb = self::db();
        if($mid == 0){
            $tb = $tb->where('mid',0);
        }else{
            $tb = $tb->where('mid','<>',0);
        }
        return $tb->get();
    }

    /**
     * 过滤掉重复的
     * @param string $gfeikd
     * @param array $feilds
     * @return mixed
     */
    public static function getList($gfeikd = 'mid',$feilds = array()){
        $tb = self::db();
        $select = '';
        foreach($feilds as $v){
            $select .= $v . ',';
        }
        $select = $select ? $select.'count(id) as c' : '* , count(id) as c';
        $tb->select(self::raw($select))->where('isSync',0);
        $tb->groupBy($gfeikd)->having('c','=',1);
        return $tb->orderBy('id','asc')->get();
    }

    public static function getLists($page = 1 , $pagesize = 10 , $feilds = array(),$where = array()){
        $tb = self::db();
        if(!empty($feilds)){
            $tb = $tb->select(self::raw($feilds));
        }
        if(is_array($where)){
            foreach ($where as $k=>$v) {
                if($k == 'gname'){
                    $tb = $tb->where($k,'like','%'.$v.'%');
                }else{
                    $tb = $tb->where($k,$v);
                }

            }
        }
        $out['total'] = $tb->count();
        $out['result'] = $tb->forPage($page,$pagesize)->orderBy('id','desc')->get();
        return $out;
    }

    /**
     * 获取映射详情
     * @param array $where
     * @return bool
     */
    public static function getDetail(array $where){
        if(!$where) return false;
        $tb = self::db();
        foreach($where as $k=>$v){
            $tb = $tb->where($k,$v);
        }
        return $tb->first();
    }

    /**
     * 保存同步状态
     * @param array $data
     * @return bool
     */
    public static function save(array $data){
        if(!$data) return false;
        $tb = self::db();
        if(!empty($data['id'])){
            $id = $data['id'];
            unset($data['id']);
            if(!$data) return false;
            return $tb->where('id',$id)->update($data);
        }else{
            $data['addtime'] = empty($data['addtime']) ? time() : $data['addtime'];
            return $tb->insertGetId($data);
        }
    }
}