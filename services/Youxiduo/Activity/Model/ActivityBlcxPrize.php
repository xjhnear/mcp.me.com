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
use Illuminate\Support\Facades\DB;

class ActivityBlcxPrize extends Model implements IModel
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
    public static function add($data){
        return self::db()->insertGetId($data);
    }

    /**
     * 判断是否有抽奖机会
     * @param $uid
     * @return mixed
     */
    public static function check($uid){
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday=mktime(23,59,59,date('m'),date('d'),date('Y'));
        return self::db()->where('uid',$uid)->whereBetween('addtime',array($beginToday,$endToday))->count();
    }

    /**
     * 查询已经获得奖项
     * @param array $prizeitem
     * @return mixed
     */
    public static function checkPrize(array $prizeitem){
        return self::db()->whereIn('prizeitem',$prizeitem)->select(DB::raw('count(id) as c,prizeitem'))->groupBy('prizeitem')->get();
    }
    //获取用户所获得的奖项
    public static function getPrize($uid){
        $time = time()-1800;
        return self::db()->where('uid',$uid)->orderBy('addtime','desc')->first();
    }

    //修改状态
    public static function save(array $data){
        if(!$data || empty($data['id'])) return false;
        $id = $data['id'];
        unset($data['id']);
        if(empty($data)) return false;
        return self::db()->where('id',$id)->update($data);
    }

}