<?php
/**
 * @package Youxiduo
 * @category Cms 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\Cms\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
/**
 * 文章类型模型类
 */
final class Arctype extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	public static function m_search($search,$pageIndex=1,$pageSize=15,$sort=array())
	{
		$out = array();
		$out['total'] = self::m_buildSearch($search)->count();
		$tb = self::m_buildSearch($search)->forPage($pageIndex,$pageSize);
	
		foreach($sort as $field=>$order){
			$tb = $tb->orderBy($field,$order);
		}
		if(!$sort){
			$tb = $tb->orderBy('id','desc');
		}
		$out['result'] = $tb->get();
		return $out;
	}
	
	public static function m_buildSearch($search)
	{
		$tb = self::db();
		if(isset($search['id']) && !empty($search['id'])){
			$tb = $tb->where('id','=',$search['id']);
		}
		if(isset($search['typename']) && !empty($search['typename'])){
			$tb = $tb->where('typename','like','%'.$search['typename'].'%');
		}
		return $tb;
	}

    /**
     * 查询当前栏目的子栏目
     * @param array $tid
     * @return mixed
     */
    public static function getSonType(array $tid){
        return self::db()->whereIn('reid',$tid)->where('ishidden','<>','1')->orderBy('sortrank')->get();
    }
}