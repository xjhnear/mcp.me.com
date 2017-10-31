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
use Illuminate\Support\Facades\Config;
/**
 * 游戏视频模型类
 */
final class GameVideo extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}	
	
    public static function getCountByGameIds($platform,$gids)
	{
		if(!$gids) return array();
		$field = $platform=='ios' ? 'gid' : 'agid';
		return self::db()->whereIn($field,$gids)->groupBy('agid')->select(self::raw($field . ' as gid,count(*) as total'))->lists('total','gid');
	}
	
    public static function getCountByGameId($platform,$gid)
	{
		if(!$gid) return 0;
		$field = $platform=='ios' ? 'gid' : 'agid';
		return self::db()->where($field,'=',$gid)->count();
	}
	
    public static function getListByGameId($platform,$gid)
	{
		if(!$gid) return array();
		$field = $platform=='ios' ? 'gid' : 'agid';
		$fields = self::raw('id,' . $field . ' as gid,title,video,ico,addtime,writer,duration');
		$result = self::db()->where($field,'=',$gid)->select($fields)->orderBy('id','desc')->get();
		
		return $result;
	}
	
    public static function getInfoById($platform,$id)
	{
		$info = self::db()->where('id','=',$id)->first();
		if($platform=='ios'){
			unset($info['agid']);
		}else{
			$info['gid'] = $info['agid'];
			unset($info['agid']);
		}
		return $info;
	}
}