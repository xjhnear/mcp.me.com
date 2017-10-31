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
 * 游戏礼包模型类
 */
final class Giftbag extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	
    public static function getCountByGameIds($gids,$only_show_enable=false)
	{
		if(!$gids) return array();
		$tb = self::db();
	    if($only_show_enable==true){
			$starttime = time();
			$endtime = time();
			$tb = $tb->where('starttime','<=',$starttime)->where('endtime','>=',$endtime);
		}
		$tb = $tb->where('is_activity','=',0)->where('is_appoint','=',0);
		return $tb->whereIn('game_id',$gids)->where('is_show','=',1)->where('last_num','>',0)->groupBy('game_id')->select(self::raw('game_id as gid,count(*) as total'))->lists('total','gid');
	}
	
	public static function getList($gid=0,$pageIndex=1,$pageSize=20,$only_show_enable=false)
	{
		$tb = self::db()->where('is_show','=',1);
		$tb = $tb->where('is_activity','=',0)->where('is_appoint','=',0);
		if($gid>0) $tb = $tb->where('game_id','=',$gid);
		if($only_show_enable==true){
			$starttime = time();//mktime(0,0,0,date('m'),date('d'),date('Y'));
			$endtime = time();//mktime(23,59,59,date('m'),date('d'),date('Y'));
			$tb = $tb->where('starttime','<=',$starttime)->where('endtime','>=',$endtime);
		}
		$result = $tb
		    ->orderBy('is_top','desc')
		    ->orderBy('sort','desc')
		    ->orderBy('ctime','desc')
		    ->orderBy('id','desc')
		    ->forPage($pageIndex,$pageSize)
		    ->get();
		return $result;
	}
	
	public static function getCount($gid=0,$only_show_enable=false)
	{
		$tb = self::db()->where('is_show','=',1);
		$tb = $tb->where('is_activity','=',0)->where('is_appoint','=',0);
		if($gid>0) $tb = $tb->where('game_id','=',$gid);
		if($only_show_enable==true){
			$starttime = time();//mktime(0,0,0,date('m'),date('d'),date('Y'));
			$endtime = time();//mktime(23,59,59,date('m'),date('d'),date('Y'));
			$tb = $tb->where('starttime','<=',$starttime)->where('endtime','>=',$endtime);
		}
		return $tb->count();
	}
	
	public static function searchCount($keyword)
	{
		return self::buildSearch($keyword)->count();
	}
	
	public static function searchResult($keyword,$pageIndex,$pageSize)
	{
		return self::buildSearch($keyword)->orderBy('id','desc')->forPage($pageIndex,$pageSize)->get();
	}
	
	protected static function buildSearch($keyword,$only_show_enable=true)
	{
		$gids = Game::db()->where('isdel','=',0)->where('shortgname','like','%'.$keyword.'%')->lists('id');
		$tb = self::db()->where('is_show','=',1);
		if($gids){
			$tb = $tb->whereIn('game_id',$gids);
		}elseif($keyword){
			$tb = $tb->where('title','like','%'.$keyword.'%');
		}
		$tb = $tb->where('is_activity','=',0)->where('is_appoint','=',0);
	    if($only_show_enable==true){
			$starttime = time();//mktime(0,0,0,date('m'),date('d'),date('Y'));
			$endtime = time();//mktime(23,59,59,date('m'),date('d'),date('Y'));
			$tb = $tb->where('starttime','<=',$starttime)->where('endtime','>=',$endtime);
		}
		
		return $tb;
	}
	
    public static function getListByGameIds($gids=0,$pageIndex=1,$pageSize=20,$only_show_enable=false)
	{
		$tb = self::db()->where('is_show','=',1);
		$tb = $tb->where('is_activity','=',0)->where('is_appoint','=',0);
		if($gids) $tb = $tb->whereIn('game_id',$gids)->where('last_num','>',0);
		if($only_show_enable==true){
			$starttime = time();//mktime(0,0,0,date('m'),date('d'),date('Y'));
			$endtime = time();//mktime(23,59,59,date('m'),date('d'),date('Y'));
			$tb = $tb->where('starttime','<=',$starttime)->where('endtime','>=',$endtime);
		}
		$result = $tb
		    ->orderBy('is_top','desc')
		    ->orderBy('sort','desc')
		    ->orderBy('ctime','desc')
		    ->orderBy('id','desc')
		    ->forPage($pageIndex,$pageSize)
		    ->get();
		return $result;
	}
	
	public static function getTotalCountByGameIds($gids=0,$only_show_enable=false)
	{
		$tb = self::db()->where('is_show','=',1);
		$tb = $tb->where('is_activity','=',0)->where('is_appoint','=',0);
		if($gids) $tb = $tb->whereIn('game_id',$gids);
		if($only_show_enable==true){
			$starttime = time();//mktime(0,0,0,date('m'),date('d'),date('Y'));
			$endtime = time();//mktime(23,59,59,date('m'),date('d'),date('Y'));
			$tb = $tb->where('starttime','<=',$starttime)->where('endtime','>=',$endtime);
		}
		return $tb->count();
	}
	
	public static function getInfoById($id,$show=false)
	{
		$tb = self::db();
		if($show==true) $tb = $tb->where('is_show','=',1);
		//$tb = $tb->where('is_activity','=',0);
		return $tb->where('id','=',$id)->first();
	}
	
    /**
	 * 礼包
	 */
	public static function getInfoByIds($ids)
	{
		if(!$ids) return array();
		$list = self::db()->whereIn('id',$ids)->get();
		$data = array();
		foreach($list as $row){
			$data[$row['id']] = $row;
		}
		return $data;
	}
	
	public static function decrementLastNum($giftbag_id)
	{
		return self::db()->where('id','=',$giftbag_id)->decrement('last_num');
	}
	
    /**
	 * 保存礼包信息
	 */
	public static function m_save($data)
	{
		if(isset($data['id']) && $data['id']>0){
			$id = $data['id'];
			unset($data['id']);
			self::db()->where('id','=',$id)->update($data);			
		}else{
			$data['ctime'] = time();
			$id = self::db()->insertGetId($data);
		}
		return $id;
	}
	
	/**
	 * 搜索礼包
	 */
	public static function m_search($search,$pageIndex=1,$pageSize=10,$sort=array())
	{
		$out = array();
		$out['total'] = self::m_buildSearch($search)->count();
		$tb = self::m_buildSearch($search)->forPage($pageIndex,$pageSize);
		if(!$sort){
			$tb = $tb->orderBy('id','desc');
		}
		foreach($sort as $field=>$order){
			$tb = $tb->orderBy($field,$order);
		}
		$result = $tb->get();
		$game_ids = array();
		foreach($result as $row){
			$game_ids[] = $row['game_id'];
		}
		if($game_ids){
			$games = Game::getListByIds($game_ids);
		}
		foreach($result as $key=>$row){
			$row['game'] = isset($games[$row['game_id']]) ? $games[$row['game_id']] : array();
			$result[$key] = $row;
		}
		$out['result'] = $result;
		return $out;
	}
	
	protected static function m_buildSearch($search)
	{
		$tb = self::db();
		if(isset($search['keyword']) && $search['keyword']){
			if(is_numeric($search['keyword'])){
				$tb = $tb->where('id','=',$search['keyword']);
			}else{
			    $tb = $tb->where('title','like','%'.$search['keyword'].'%');
			}
		}
	    if(isset($search['game_id']) && $search['game_id']>0){
			$tb = $tb->where('game_id','=',$search['game_id']);
		}
		
	    if(isset($search['is_activity']) && $search['is_activity']==1){
			$tb = $tb->where('is_activity','=',1);
		}
		
	    if(isset($search['is_appoint']) && $search['is_appoint']==1){
			$tb = $tb->where('is_appoint','=',1);
		}
		
		if(isset($search['from_type']) && intval($search['from_type'])>0){
			$tb = $tb->where('from_type','=',(int)$search['from_type']);
		}
		
		return $tb;
	}
	
	/**
	 * 礼包
	 */
	public static function m_getInfoByIds($ids)
	{
		if(!$ids) return array();
		$list = self::db()->whereIn('id',$ids)->get();
		$data = array();
		foreach($list as $row){
			$data[$row['id']] = $row;
		}
		return $data;
	}
	
	/**
	 * 获取礼包信息
	 */
	public static function m_getInfo($id)
	{
		$info = self::db()->where('id','=',$id)->first();
		$info['condition'] = json_decode($info['condition'],true);
		return $info;
	}

    public static function m_delete($id){
        if(!$id) return false;
        return self::db()->where('id',$id)->delete();
    }
}