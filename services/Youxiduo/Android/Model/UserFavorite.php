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
 * 用户收藏模型类
 */
final class UserFavorite extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	const LINK_TYPE_NEWS    = 'news';
	const LINK_TYPE_GUIDE   = 'guide';
	const LINK_TYPE_OPINION = 'opinion';
	const LINK_TYPE_NEWGAME = 'newgame';
	const LINK_TYPE_GAMEVIDEO = 'video';
	
	public static function addUserFavorite($uid,$link_id,$link_type,$gid)
	{
		$exists = self::db()->where('link_id','=',$link_id)->where('link_type','=',$link_type)->where('uid','=',$uid)->first();
		if($exists) return false;
		$data['uid'] = $uid;
		$data['link_id'] = $link_id;
		$data['link_type'] = $link_type; 
		$data['ctime'] = time();
		$data['agid'] = $gid;
		self::db()->insertGetId($data);
		return true;
	}
	
	public static function removeUserFavorite($uid,$link_id,$link_type)
	{
		return self::db()->where('link_id','=',$link_id)->where('link_type','=',$link_type)->where('uid','=',$uid)->delete();
	}
	
	public static function isExistsUserFavorite($uid,$link_id,$link_type)
	{
		$exists = self::db()->where('link_id','=',$link_id)->where('link_type','=',$link_type)->where('uid','=',$uid)->first();
		return $exists ? true : false;
	}
	
	public static function getUserFavorite($uid,$pageIndex=1,$pageSize=20,$gid=0)
	{
		$tb = self::db()->where('uid','=',$uid);
		if($gid>0) $tb = $tb->where('agid','=',$gid);
		return $tb->orderBy('id','desc')->forPage($pageIndex,$pageSize)->get();
	}
	
	public static function getUserFavoriteCount($uid,$gid=0)
	{
		$tb = self::db()->where('uid','=',$uid);
		if($gid>0) $tb = $tb->where('agid','=',$gid);
		return $tb->count();
	}
}