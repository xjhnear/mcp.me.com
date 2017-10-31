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
 * 赞/围观模型类
 */
final class Like extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function isExists($uid,$target_id,$target_table)
	{
		$exists = self::db()->where('target_id','=',$target_id)->where('target_table','=',$target_table)->where('uid','=',$uid)->first();
		return $exists ? true : false;
	}
	
	public static function doLike($uid,$target_id,$target_table)
	{
		$data = array('uid'=>$uid,'target_id'=>$target_id,'target_table'=>$target_table,'ctime'=>time());
		return self::db()->insert($data) ? true : false;
	}
	
    public static function unDoLike($uid,$target_id,$target_table)
	{
		$res = self::db()->where('target_id','=',$target_id)->where('target_table','=',$target_table)->where('uid','=',$uid)->delete();
		return $res ? true : false;
	}
	
	public static function getLikeCount($target_id,$target_table)
	{
		return self::db()->where('target_id','=',$target_id)->where('target_table','=',$target_table)->count();
	}
	
	public static function getLikeList($target_id,$target_table,$pageIndex=1,$pageSize=10)
	{
		return self::db()->where('target_id','=',$target_id)->where('target_table','=',$target_table)->orderBy('id','desc')->forPage($pageIndex,$pageSize)->lists('uid');
	}
	
    public static function getLikeCountByTids($target_ids,$target_table)
	{
		return self::db()->whereIn('target_id',$target_ids)->where('target_table','=',$target_table)->groupBy('target_id')->select(self::raw('target_id,count(*) as totalCount'))->lists('totalCount','target_id');
	}
	
	public static function getLikeListByTids($target_ids,$target_table)
	{
		$result = self::db()->whereIn('target_id',$target_ids)->where('target_table','=',$target_table)->orderBy('target_id','asc')->orderBy('id','desc')->get();
		if($result){
			$out = array();
			foreach($result as $row){
				$out[$row['target_id']][] = $row['uid'];
			}
			return $out;
		}
		return array();
	}
}