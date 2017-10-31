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
namespace Youxiduo\V4\Game\Model;

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

use Youxiduo\Helper\Utility;
/**
 * 游戏区服模型类
 */
final class GameArea extends Model implements IModel
{	
	public static $AREA_TYPE_LIST = array('1'=>'IOS正版','2'=>'越狱(itools)','3'=>'越狱（同步推）','4'=>'越狱（91助手）','5'=>'越狱（PP助手）');
	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getGameAreaList($game_id,$uid,$platform)
	{
		$res = self::db()->where('game_id','=',$game_id)->where('platform','=',$platform)->where(function($query)use($uid){
			$query = $query->where('uid','=',$uid)->orWhere('is_open','=',1);
		})->orderBy('type','asc')->orderBy('sort','asc')->orderBy('id','asc')->get();
		
		return $res;
	}
	
	public static function getGameAreaListByIds($ids)
	{
		if(!$ids) return array();
		$result = self::db()->whereIn('id',$ids)->get();
		$out = array();
		foreach($result as $row){
			$out[$row['id']] = $row;
		}
		return $out;
	}
	
	public static function searchCount($search)
	{
		return self::buildSearch($search)->count();
	}
	
	public static function searchList($search,$pageIndex=1,$pageSize=10)
	{
		return self::buildSearch($search)->orderBy('type','asc')->orderBy('id','asc')->forPage($pageIndex,$pageSize)->get();
	}
	
	protected static function buildSearch($search)
	{
		$tb = self::db()->where('platform','=','ios');
		if(isset($search['game_id']) && $search['game_id']){
			$tb = $tb->where('game_id','=',$search['game_id']);
		}
		return $tb;
	}
	
	public static function getInfo($id)
	{
		return self::db()->where('id','=',$id)->first();
	}
	
	public static function save($data)
	{
	    if(isset($data['id']) && $data['id']){
    		$id = $data['id'];
    		unset($data['id']);
    		$data['ctime'] = time();
    		return self::db()->where('id','=',$id)->update($data);
    	}else{
    		$data['ctime'] = time();
    		return self::db()->insertGetId($data);
    	}
	}
}