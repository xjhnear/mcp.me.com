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

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
use Illuminate\Support\Facades\Config;

/**
 * 开测游戏模型类
 */
final class GameFirst extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}

    //查询一周前或一周后内开测游戏
    public static function InWeekQuery($query,$cur_datetime,$future_week,$history_week){
        $query->orWhere(function($query)use($cur_datetime,$future_week)
        {
            $query->where('addtime', '>', $cur_datetime)
                ->where('addtime', '<=', $future_week);
        })
        ->orWhere(function($query)use($cur_datetime,$history_week)
        {
            $query->where('addtime', '>=', $history_week)
                ->where('addtime', '<', $cur_datetime);
        });
    }
    //查询热门和今日开测游戏
    protected static function CurrentDayHot($query,$cur_datetime){
        $query->orWhere(function($query)use($cur_datetime)
        {
            $query->where('istop',1)
                ->orWhere('addtime',$cur_datetime);
        });
    }

    protected static function BuildGame(array $res){
        if(!$res) return array();
        $result = array();
        foreach($res as $k=>$newgame){
            $agid = $newgame['agid'];
            $list = Game::getListByIds(array($agid));
            $gname = $gicon = $gtype = $gdel = '';
            if($list && $list[$agid]){
                $game = $list[$agid];
                $gname = $game['shortgname'];
                $gicon = $game['ico'];
                $gtype = $game['type'];
                $gdel = $game['isdel'];
            }
            $res[$k]['shortgname'] = $gname;
            $res[$k]['ico'] = $gicon;
            $res[$k]['type'] = $gtype;
            $res[$k]['isdel'] = $gdel;
        }
        return $res;
    }

    protected static function currentDayAndHot($cur_datetime){
        $query = self::db()->where('agid','>',0);
        $query = $query->Where(function($query)use($cur_datetime)
        {
            GameFirst::CurrentDayHot($query,$cur_datetime);
        });
        return $query;
    }

    /**
     * 查询一周内开测游戏
     * 查询总数(热门或今日 未来一周 过去一周)
     * @value Type DateTime
     * @param $cur_datetime 当前时间
     * @param 未来一周时间 $future_week 过去一周时间
     * @param $history_week
     * @return array|int
     */
    public static function getInWeekCount($cur_datetime,$future_week,$history_week)
    {
        $query = self::db()->where('agid','>',0);
        $query = $query->Where(function($query)use($cur_datetime,$future_week,$history_week)
                        {
                            GameFirst::CurrentDayHot($query,$cur_datetime);
                            GameFirst::InWeekQuery($query,$cur_datetime,$future_week,$history_week);
                        });
        $result = $query->select('agid')->get();
        if($result){
            $result = array_flatten($result);
            return Game::getCountByIds($result);
        }
        return 0;
    }

    /**
     * 查询热门和今日开测的总数
     */
    public static function getCurrentDayHotCount($cur_datetime){
        $query = self::db()->where('agid','>',0);
        $query = $query->Where(function($query)use($cur_datetime)
        {
            GameFirst::CurrentDayHot($query,$cur_datetime);
        });

        $result = $query->select('agid')->get();
        if($result){
            $result = array_flatten($result);
            return Game::getCountByIds($result);
        }
        return 0;
    }

    public static function getCurrentDayHot($cur_datetime,$pageIndex,$pageSize){
        $query = self::currentDayAndHot($cur_datetime);
        $query->orderby('istop','desc')
                ->orderby('addtime','asc')
                ->orderby('id','desc');
        $result = $query->forpage($pageIndex,$pageSize)->get();
        $result = self::BuildGame($result);
        return $result;
    }

    //如果当前页的数据不足整页就再取其他的数据补充为一整页
    public static function getInWeekNotHot($cur_datetime,$future_week,$history_week,$pageIndex,$pageSize){
        $query = self::db()->where('agid','>',0)->where('istop','!=',1);
        $query = $query->Where(function($query)use($cur_datetime,$future_week,$history_week)
        {
            GameFirst::InWeekQuery($query,$cur_datetime,$future_week,$history_week);
        });
        $query->orderby('addtime','desc')
            ->orderby('id','desc');
        $result = $query->forpage($pageIndex,$pageSize)->get();
        $result = self::BuildGame($result);
        return $result;
    }

    public static function currentDayBeforeOrAfter($pageIndex,$pageSize,array $wheres=array()){
        $query = self::db()->where('agid','>',0);
        if($wheres){
            foreach($wheres as $w){
                $query->where($w[0],$w[1],$w[2]);
            }
        }else{
            $query->orderby('istop','desc');
        }
        $query->orderby('addtime','desc')
            ->orderby('id','desc');
        $count = $query->count();
        $result = $query->forpage($pageIndex,$pageSize)->get();
        $result = self::BuildGame($result);
        return array('result'=>$result,'count'=>$count);
    }

    public static function getList($page=1,$size=10,$platform,$title=''){
        $query = self::db();
        if($title) $query->where('title',$title);
        if($platform == 'ios'){
            $query->where('agid',0);
        }else{
            $query->where('gid',0);
        }
        return $query->forPage($page,$size)->orderBy('id','desc')->get();
    }

    public static function getListCount($platform,$title=''){
        $query = self::db();
        if($title) $query->where('title',$title);
        if($platform == 'ios'){
            $query->where('agid',0);
        }else{
            $query->where('gid',0);
        }
        return $query->count();
    }

    public static function getInfoById($id){
        if(!$id) return false;
        return self::db()->where('id',$id)->first();
    }

    public static function add($data){
        if(!$data) return false;
        return self::db()->insert($data);
    }

    public static function update($id,$data){
        if(!$id || !$data) return false;
        return self::db()->where('id',$id)->update($data);
    }

    public static function delete($id){
        if(!$id) return false;
        return self::db()->where('id',$id)->delete();
    }
}