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
/**
 * 游戏专题游戏模型类
 */
final class News extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
    public static function getCountByGameIds($gids)
	{
		if(!$gids) return array();
		return self::db()->whereIn('agid',$gids)->where('pid','<=',0)->groupBy('agid')->select(self::raw('agid as gid,count(*) as total'))->lists('total','gid');
	}
	
	public static function getListByIds($ids)
	{
		if(!$ids) return array();
		$fields = array('id','agid','title','addtime','pid','commenttimes');
		$result = self::db()->whereIn('id',$ids)->select($fields)->orderBy('id','desc')->get();
		$out = array();
		foreach($result as $row){
			$out[$row['id']] = $row;
		}
		return $out;
	}
	
	public static function getShortInfoById($id)
	{
		//$fields = array('id','agid','title');
        $fields = array('id','title','agid','writer','addtime','content');
		return self::db()->where('id','=',$id)->where('agid','>',0)->select($fields)->first();
	}

    public static function getGameNews($gid)
    {
        $out = array();
        $fields = array('id', 'pid', 'title', 'addtime', 'content');
        $result = self::db()->where('agid','=',$gid)->where('pid','<=',0)->select($fields)->orderBy('sort','desc')->orderBy('addtime','desc')->get();
        if ($result){
            foreach ($result as $k => $v){
                $out[$k]['gnid'] = $v['id'];
                $out[$k]['title'] = $v['title'];
                if ($v['pid'] == -1){
                    $row = self::db()->where('pid','=',$v['id'])->select('title','addtime')->orderBy('addtime','desc')->first();
                    $out[$k]['series'] = true;
                    $out[$k]['updatetime'] = date('Y-m-d H:i:s', $row['addtime']);
                }else{
                    $out[$k]['series'] = false;
                    $out[$k]['updatetime'] = date('Y-m-d H:i:s', $v['addtime']);
                }
                $out[$k]['ptitle'] = $v['title'];
                $out[$k]['video'] = VideoGame::_isExistVideo($v['content']);
            }
        }
        return $out;
    }

    public static function getArticleSeriesById($id)
    {
        $out = array();
        $fields = array('id','title','content','addtime');
        $rs = self::db()->where('agid','>',0)->where('pid','=',$id)->orderBy('sort','desc')->orderBy('addtime','desc')->get();
        if ($rs){
            foreach ($rs as $k => $v){
                $out[$k]['gnid'] = $v['id'];
                $out[$k]['title'] = $v['title'];
                $out[$k]['video'] = VideoGame::_isExistVideo($v['content']);
                $out[$k]['updatetime'] = date('Y-m-d H:i:s', $v['addtime']);
            }
        }
        return $out;
    }
}