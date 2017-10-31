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
 * 新游模型类
 */
final class GameBeta extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
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
		if(isset($search['platform'])){
			$tb = $search['platform']=='ios' ? $tb->where('gid','>',0) : $tb->where('agid','>',0);
		}
		if(isset($search['condition'])){
			foreach($search['condition'] as $cond){
				$tb = $tb->where($cond['field'],$cond['logic'],$cond['value']);
			}
		}
		return $tb;
	}
}