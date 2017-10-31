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
use Youxiduo\Helper\Utility;
/**
 * 游戏视频模型类
 */
final class GameVideo extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}	
	
    public static function getCountByGameIds($gids)
	{
		if(!$gids) return array();
		return self::db()->whereIn('agid',$gids)->groupBy('agid')->select(self::raw('agid as gid,count(*) as total'))->lists('total','gid');
	}
	
    public static function getListByIds($ids)
	{
		if(!$ids) return array();
		$fields = array('id','agid','title','addtime');
		$result = self::db()->whereIn('id',$ids)->select($fields)->orderBy('id','desc')->get();
		$out = array();
		foreach($result as $row){
			$out[$row['id']] = $row;
		}
		return $out;
	}
	
    public static function getShortInfoById($id)
	{
		$fields = array('id','agid','title');
		return self::db()->where('id','=',$id)->select($fields)->first();
	}

    public static function getGameVideos($gid)
    {
        $out = array();
        $fields = array('id','title','writer','ico','addtime');
        $result = self::db()->where('agid','=',$gid)->where('type','=',1)->select($fields)->orderBy('id','desc')->take(3)->get();
        if ($result) {
            foreach ($result as $k => $v){
                $out[$k]['gvid'] = $v['id'];
                $out[$k]['title'] = $v['title'];
                $out[$k]['editor'] = $v['writer'];
                $out[$k]['img'] = Utility::getImageUrl($v['ico']);
                $out[$k]['updatetime'] = date("Y-m-d H:i:s",$v['addtime']);
            }
        }
        return $out;
    }

    public static function getGameVideoDetail($gvid)
    {
        $out = array();
        $fields = array('id','title','writer','video','addtime','agid','ico');
        $row = self::db()->where("agid",">",0)->where("id",$gvid)->first();
        if ($row){
            $out['gvid'] = $row['id'];
            $out['title'] = $row['title'];
            $out['editor'] = $row['writer'];
            $out['url'] = $row['video'];
            //$out['body'] = youku_h5($row['video']);
            $out['updatetime'] = date("Y-m-d H:i:s", $row['addtime']);

            $out['agid'] = $row['agid'];
            $out['ico'] = Utility::getImageUrl($row['ico']);
        }
        return $out;
    }
}