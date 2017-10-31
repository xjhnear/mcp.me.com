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
 * 游戏模型类
 */
final class IosGame extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
	public static function getInfoById($gid)
	{
		return self::db()->where('id','=',$gid)->first();
	}

    /**
     * 通过游戏名来查询游戏
     * @param $gname
     * @return array
     */
    public static function getInfoPassName($gname){
        if(!$gname) return array();
        return self::db()->where('gname',$gname)->get();
    }

	public static function getMultiInfoById($gids,$format=false)
	{
		if(!$gids) return array();
		$result = self::db()->whereIn('id',$gids)->get();
		if($format==false) return $result;
		$out = array();
		foreach($result as $row){
			$out[$row['id']] = $row;
		}
		return $out;
	}
	
	public static function search($search,$pageIndex=1,$pageSize=10,$order=array())
	{
		$total = self::buildSearch($search)->count();
		$tb = self::buildSearch($search);
		foreach($order as $field=>$sort)
		{
			$tb = $tb->orderBy($field,$sort);
		}
		$result = $tb->forPage($pageIndex,$pageSize)->get();
		return array('result'=>$result,'totalCount'=>$total);
	}
	
	protected static function buildSearch($search)
	{
		$tb = self::db();
		if(!isset($search['isdel'])){
			$tb = $tb->where('isdel','=',0);
		}
		
		return $tb;
	}
}