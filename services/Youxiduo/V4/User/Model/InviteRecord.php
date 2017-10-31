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
namespace Youxiduo\V4\User\Model;

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

use Youxiduo\Helper\Utility;
/**
 * 邀请记录模型类
 */
final class InviteRecord extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	public static function findCount($search)
	{
		return self::buildFindList($search)->count();
	}
	public static function findList($search,$pageIndex,$pageSize)
	{		
		return self::buildFindList($search)->forPage($pageIndex,$pageSize)->orderBy('id','desc')->get();
	}
	
	protected static function buildFindList($search)
	{
		$tb = self::db()->where('oldid','=',$search['uid']);
		return $tb;
	}
	
	public static function findMyInviter($uid)
	{
		$info = self::db()->where('newid','=',$uid)->first();
		if($info) return $info['oldid'];
		return 0;
	}
	
	public static function rankList($search,$pageIndex=1,$pageSize=10)
	{
		$tb = self::db()->select(self::raw('oldid as uid,count(*) as total'));
		if(isset($search['start_time'])){
			$tb = $tb->where('ctime','>=',strtotime($search['start_time']));
		}
		
	    if(isset($search['end_time'])){
			$tb = $tb->where('ctime','<=',strtotime($search['end_time']));
		}
		return $tb->groupBy('oldid')->orderBy('total','desc')->forPage($pageIndex,$pageSize)->get();
	}
}