<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/4/22
 * Time: 18:09
 */
namespace Youxiduo\Activity\Model;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

class TtxdVote extends Model implements IModel
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    /**
     * 添加投票
     * @param $data
     * @return mixed
     */
    public static function add($data)
    {
        return self::db()->insertGetId($data);
    }

    public static function getVoteCount(array $data){
        if(!$data || !is_array($data)) return false;
        $tb = self::db();
        foreach($data as $k=>$v){
            if($k == 'starttime'){
                $tb = $tb->where('addtime','>',$v);
            }elseif($k == 'endtime'){
                $tb = $tb->where('addtime','<',$v);
            }else{
                $tb = $tb->where($k,$v);
            }
        }
        return $tb->count();
    }
}