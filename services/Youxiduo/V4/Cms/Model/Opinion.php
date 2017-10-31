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
namespace Youxiduo\V4\Cms\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
/**
 * 文章评测模型类
 */
final class Opinion extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	/**
	 * 获取文章列表
	 * @param string $platform
	 * @param int $type_id
	 * @param int $game_id
	 * @param int $series
	 * @param string $sort
	 * @param int $pageIndex
	 * @param int $pageSize
	 * 
	 * @return array 
	 */
	public static function getListByCond($platform,$type_id,$game_id=0,$series,$sort,$pageIndex=1,$pageSize=10)
	{
		$field = 'id';
		if($sort=='date') $field = 'addtime';
		if($sort=='hot') $field = 'commenttimes';
		$fields = self::raw('id,agid,gid,ftitle as title,addtime,pid,commenttimes,writer,editor,litpic,litpic2,litpic3');
		$total = self::buildCond($platform, $type_id, $game_id,$series)->count();
		$result = self::buildCond($platform, $type_id, $game_id,$series)->select($fields)->orderBy($field,'desc')->forPage($pageIndex,$pageSize)->get();
		return array('result'=>$result,'totalCount'=>$total);		
	}
	
	/**
	 * 构造条件
	 */
	protected static function buildCond($platform,$type_id,$game_id,$series=0)
	{
		$tb = self::db();
		if($platform=='ios'){
			if($game_id>0) $tb = $tb->where('gid','=',$game_id); 
		}elseif($platform=='android'){
			if($game_id>0) $tb = $tb->where('agid','=',$game_id);
		}
		
		if($type_id){
			//$tb = $tb->where('type_id','=',$type_id);
		}
		
		if($series){
			$tb = $tb->where('pid','=',$series);
		}else{
			$tb = $tb->where('pid','<=',0);
		}
		
		return $tb;
	}
	
    public static function getDetailById($platform,$id)
	{
		$fields = self::raw('id,agid,gid,ftitle as title,addtime,pid,commenttimes,writer,content,editor');
		$info = self::db()->where('id','=',$id)->select($fields)->first();
		return $info;
	}
	
	public static function getCountByGameIds($gids)
	{
		if(!$gids) return array();
		return self::db()->whereIn('agid',$gids)->where('pid','<=',0)->groupBy('agid')->select(self::raw('agid as gid,count(*) as total'))->lists('total','gid');
	}
	
    public static function getListByIds($ids)
	{
		if(!$ids) return array();
		$fields = array('id','agid','ftitle as title','addtime','pid','commenttimes');
		$result = self::db()->whereIn('id',$ids)->select($fields)->orderBy('id','desc')->get();
		$out = array();
		foreach($result as $row){
			$out[$row['id']] = $row;
		}
		return $out;
	}
	
    public static function getShortInfoById($id)
	{
		//$fields = array('id','agid','ftitle');
        $fields = array('id','ftitle','agid','writer','addtime','content');
		return self::db()->where('id','=',$id)->where('agid','>',0)->select($fields)->first();
	}

    /**
     * 获取游戏评测列表
     */
    public static function getGameOpinion($gid=0){
        $out = array();
        $result = self::db()->where('agid',$gid)->where('pid','<=',0)->select('id', 'ftitle', 'pid', 'content', 'addtime')->orderby('sort','desc')->orderby('addtime','desc')->get();
        if($result){
            foreach($result as $k=>$v){
                $out[$k]['goid'] = $v['id'];
                $out[$k]['title'] = $v['ftitle'];
                if ($v['pid'] == -1){
                    $row = self::db()->where('pid','=',$v['id'])->select("addtime")->orderby("addtime","desc")->first();
                    $out[$k]['updatetime'] = date("Y-m-d H:i:s", $row['addtime']);
                    $out[$k]['series'] = true;
                }else{
                    $out[$k]['series'] = false;
                    $out[$k]['updatetime'] = date("Y-m-d H:i:s", $v['addtime']);
                }
                $out[$k]['video'] = VideoGame::_isExistVideo($v['content']);
            }
        }
        return $out;
    }

    public static function getArticleSeriesById($id){
        $out = array();
        $rs = self::db()->where("agid",">",0)->where("pid",$id)->orderby("sort","desc")->orderby("addtime","desc")->get();
        if ($rs){
            foreach ($rs as $k => $v){
                $out[$k]['goid'] = $v['id'];
                $out[$k]['title'] = $v['ftitle'];
                $out[$k]['video'] = VideoGame::_isExistVideo($v['content']);
                $out[$k]['updatetime'] = date("Y-m-d H:i:s", $v['addtime']);
            }
        }
        return $out;
    }
}