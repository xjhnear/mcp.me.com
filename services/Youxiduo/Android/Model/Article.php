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
 * 文章搜索模型类
 */
final class Article extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	
    public static function search($keyword,$pageIndex=1,$pageSize=10,$gid=0)
	{
		$fields = array('id','aid','title','addtime','cate_id');
		$tb = self::db()->where('title','like','%'.$keyword.'%');
		if($gid>0) $tb = $tb->where('agid','=',$gid);
		return $tb->orderBy('id','desc')->forPage($pageIndex,$pageSize)->select($fields)->orderBy('addtime','desc')->get();
	}
	
	public static function searchCount($keyword,$gid=0)
	{
		$tb = self::db()->where('title','like','%'.$keyword.'%');
		if($gid>0) $tb = $tb->where('agid','=',$gid);
		return $tb->count();
	}
}