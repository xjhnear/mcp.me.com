<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/4/15
 * Time: 16:08
 */
namespace Youxiduo\Activity\Model;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

class ActivityComment extends Model implements IModel
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    /**
     * 添加评论
     * @param $data
     * @return mixed
     */
    public static function add($data){
        return self::db()->insertGetId($data);
    }

    public static function getLists(array $seach){
        $tb = self::db();
        foreach ($seach as $k => $v) {
            $tb = $tb->where($k,$v);
        }
        return $tb->orderBy('addtime','desc')->get();
    }
}