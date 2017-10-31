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
final class NewGame extends Model implements IModel
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
		if($sort=='date') $field = 'adddate';
		if($sort=='hot') $field = 'commenttimes';
		$fields = self::raw('id,title,addtime,commenttimes,editor,litpic,litpic2,litpic3');
		$total = self::buildCond($platform, $type_id, $game_id,$series)->count();
		$result = self::buildCond($platform, $type_id, $game_id,$series)->select($fields)->orderBy($field,'desc')->orderBy('sort','desc')->orderBy('addtime','desc')->forPage($pageIndex,$pageSize)->get();
		foreach($result as $key=>$row){
			$row['gid'] = 0;
			$row['agid'] = 0;
			$row['pid'] = 0;
			$row['writer'] = '';
			$result[$key] = $row;
		}
		return array('result'=>$result,'totalCount'=>$total);		
	}
	
	/**
	 * 构造条件
	 */
	protected static function buildCond($platform,$type_id,$game_id,$series=0)
	{
		$tb = self::db()->where('isshow','=',1);
		if($platform=='ios'){
			if($game_id>0) $tb = $tb->where('gid','=',$game_id); 
		}elseif($platform=='android'){
			if($game_id>0) $tb = $tb->where('agid','=',$game_id);
		}
		
		if($type_id){
			//$tb = $tb->where('type_id','=',$type_id);
		}
		
		return $tb;
	}
	
    public static function getDetailById($platform,$id)
	{
		$fields = self::raw('id,title,addtime,commenttimes,content,editor');
		$info = self::db()->where('id','=',$id)->select($fields)->first();
		if($info){
			$info['gid'] = 0;
			$info['agid'] = 0;
			$info['pid'] = 0;
			$info['writer'] = '';
		}
		return $info;
	}
}