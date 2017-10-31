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
 * 活动模型类
 */
final class Activity extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}	
	
	public static function getList($pageIndex,$pageSize,$gid=0)
	{
		$tb = self::db()->where('apptype','!=',1)->where('isshow','=',1);
		if($gid>0) $tb = $tb->where('agid','=',$gid);
		$result =  $tb
		    ->orderBy('istop','desc')
		    ->orderBy('addtime','desc')
		    ->orderBy('sort','desc')
		    ->forPage($pageIndex,$pageSize)
		    ->get();
		return $result;
	}
	
	public static function getCount($gid=0)
	{
		$tb = self::db()->where('apptype','!=',1)->where('isshow','=',1);
		if($gid>0) $tb = $tb->where('agid','=',$gid);
		return $tb->count();
	}
	
    public static function getListByGameIds($pageIndex,$pageSize,$gids)
	{
		$tb = self::db()->where('apptype','!=',1)->where('isshow','=',1)->where('endtime','>',time());
		if($gids) $tb = $tb->where(function($query)use($gids){
		    if($gids) $query = $query->whereIn('agid',$gids);
			/*
		    $query = $query->orWhere(function($query){
		        $query = $query->where('istop','=',0);
		    });
			*/
		});
		$result =  $tb
		    ->orderBy('istop','desc')
		    ->orderBy('addtime','desc')
		    ->orderBy('sort','desc')
		    ->forPage($pageIndex,$pageSize)
		    ->get();
		return $result;		
	}
	
	public static function getListByTimes($starttime,$endtime)
	{
	    $tb = self::db()->where('apptype','!=',1)->where('isshow','=',1)->where('starttime','>=',$starttime)->where('endtime','<',$endtime);

	    $result =  $tb
	    ->orderBy('istop','desc')
	    ->orderBy('endtime','desc')
	    ->orderBy('sort','desc')
	    ->get();
	    return $result;
	}
	
	public static function searchCount($keyword)
	{
		return self::buildSearch($keyword)->count();
	}
	
    public static function searchResult($keyword,$pageIndex=1,$pageSize=10)
	{
		return self::buildSearch($keyword)->orderBy('id','desc')->forPage($pageIndex,$pageSize)->get();
	}
	
	protected static function buildSearch($keyword)
	{
		$gids = Game::db()->where('isdel','=',0)->where('shortgname','like','%'.$keyword.'%')->lists('id');
		$tb = self::db()->where('apptype','!=',1)->where('isshow','=',1);
		
		if($gids){
			$tb = $tb->whereIn('agid',$gids);
		}elseif($keyword){
			$tb = $tb->where('title','like','%'.$keyword.'%');
		}
		return $tb;
	}
	
	public static function getTotalCountByGameIds($gids)
	{
		$tb = self::db()->where('apptype','!=',1)->where('isshow','=',1)->where('endtime','>',time());;
		if($gids){
			$tb = $tb->whereIn('agid',$gids);
		}
		/*
		if($gids) $tb = $tb->where(function($query)use($gids){
		    $query = $query->whereIn('agid',$gids)->orWhere(function($query){
		        $query = $query->where('istop','=',0)->where('endtime','>',time());
		    });
		});
		*/
		return $tb->count();
	}
	
	public static function getInfoById($id)
	{
		$info = self::db()->where('id','=',$id)->where('apptype','!=',1)->where('isshow','=',1)->first();
		
		return $info;
	}
	
    public static function getCountByGameIds($gids)
	{
		if(!$gids) return array();
		$starttime = time();//mktime(0,0,0,date('m'),date('d'),date('Y'));
		$endtime = time();//mktime(23,59,59,date('m'),date('d'),date('Y'));
		return self::db()->whereIn('agid',$gids)->where('starttime','<=',$starttime)->where('endtime','>=',$endtime)->groupBy('agid')->select(self::raw('agid as gid,count(*) as total'))->lists('total','gid');
	}

    public static function getShareInfoById($id){
        $fields = array('gid','agid', 'title', 'gname','pic','writer');
        $info = self::db()->where('id',$id)->where(function($query){
            $query->where('apptype','&',2)->where('apptype','!=',0);
        })->select($fields)->first();
        return $info;
    }
    
    public static function m_searchCount($search)
    {
    	return self::m_buildSearch($search)->count();
    }
    
    public static function m_searchList($search,$pageIndex=1,$pageSize=10)
    {
    	return self::m_buildSearch($search)->orderBy('istop','desc')->orderBy('sort','desc')->orderBy('id','desc')->forPage($pageIndex,$pageSize)->get();
    }
    
    protected static function m_buildSearch($search)
    {
    	$tb = self::db()->where('apptype','!=',0);    	
    	if(isset($search['keyword']) && $search['keyword']){
    		if(is_numeric($search['keyword'])){
    			$tb = $tb->where('id','=',$search['keyword']);
    		}else{
    		    $tb = $tb->where('title','like','%'.$search['keyword'].'%');
    		}
    	}
    	if(isset($search['game_id']) && $search['game_id']){
    		$tb = $tb->where('agid','=',$search['game_id']);
    	}
    	
    	if(isset($search['is_top']) && $search['is_top']){
    		$tb = $tb->where('istop','=',$search['is_top']);
    	}
    	
    	return $tb;
    }
    
    public static function m_getInfo($id)
    {
    	return self::db()->where('id','=',$id)->first();
    }
    
    public static function m_save($data)
    {
    	if(isset($data['id']) && $data['id']){
    		$id = $data['id'];
    		unset($data['id']);
    		$data['updatetime'] = time();
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