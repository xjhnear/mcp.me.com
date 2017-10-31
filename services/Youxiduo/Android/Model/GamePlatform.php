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
 * 游戏包模型类
 */
final class GamePlatform extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function m_search($search,$pageIndex=1,$pageSize=10)
	{
		$total = self::m_buildSearch($search)->count();
		$result = self::m_buildSearch($search)->orderBy('id','desc')->get();
		return array('result'=>$result,'total'=>$total);
	}
	
	protected static function m_buildSearch($search)
	{
		$tb = self::db();
		if(isset($search['game_id']) && $search['game_id']){
			$tb = $tb->where('game_id','=',$search['game_id']);
		}
		return $tb;
	}
	
	public static function m_getInfo($id)
	{
		$info = self::db()->where('id','=',$id)->first();
		return $info;
	}
	
	public static function m_save($data)
	{
	    if(isset($data['id']) && $data['id']){
			$id = $data['id'];
			unset($data['id']);
			return self::db()->where('id','=',$id)->update($data);
		}else{
			$data['addtime'] = time();
			return self::db()->insertGetId($data);
		}
	}
	
	public static function m_delete($id)
	{
		return self::db()->where('id','=',$id)->delete();
	}
}