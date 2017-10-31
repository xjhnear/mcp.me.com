<?php
namespace modules\forum\models;
use Yxd\Services\Cms\GameService;

use Illuminate\Support\Facades\Event;
use Yxd\Modules\Core\BaseModel;

class ForumModel extends BaseModel
{
	public static function doOpen($game_id)
	{
		$count = self::dbClubSlave()->table('forum')->where('gid','=',$game_id)->count();
		if($count>0){
			return -1;
		}else{
			$data = array(
			    'gid'=>$game_id
			);
			return self::dbClubSlave()->table('forum')->insert($data);
		}
	}
	
	public static function doClose($game_id)
	{
		return self::dbClubSlave()->table('forum')->where('gid','=',$game_id)->delete();
	}
	
	public static function search($search,$page=1,$size=10,$sort=array())
	{
		$out = array();
		$out['total'] = self::buildSearch($search)->count();
		$tb = self::buildSearch($search)->forPage($page,$size);
		
		foreach($sort as $field=>$order){
			$tb = $tb->orderBy($field,$order);
		}
		if(!$sort){
			$tb = $tb->orderBy('gid','desc');
		}
		$forums = $tb->get();
		$gids = array();
		foreach($forums as $row){
			$gids[] = $row['gid'];
		}
		$games = GameService::getGamesByIds($gids);
		foreach($forums as $key=>$row){
			if(!isset($games[$row['gid']])) continue;
			$row = array_merge($row,$games[$row['gid']]);
			$forums[$key] = $row;
		}
		$out['result'] = $forums;
		return $out;
	}
	
    public static function buildSearch($search)
	{
		$tb = self::dbClubSlave()->table('forum');
		if(isset($search['id']) && $search['id']){						
			return $tb = $tb->where('gid','=',$search['id']);
		}
		
		if(isset($search['gname']) && $search['gname']){
			$gids = self::dbCmsSlave()->table('games')->where('isdel','=',0)->where('gname','like','%'.$search['gname'].'%')->lists('id');
			if($gids){
				$tb = $tb->whereIn('gid',$gids);
			}
		}
				
		return $tb;
	}
	
	public static function getExpeditionList($page=1,$size=10)
	{
		$total = self::dbCmsSlave()->table('game_expedition')->count();
		$result = self::dbCmsSlave()->table('game_expedition')->forPage($page,$size)->orderBy('sort','asc')->orderBy('addtime','desc')->get();
		return array('result'=>$result,'total'=>$total);
	}
	
	public static function getExpeditionInfo($id)
	{
		return self::dbCmsSlave()->table('game_expedition')->where('id','=',$id)->first();
	}
	
	public static function saveExpedition($data)
	{
		if(isset($data['id']) && $data['id']){
			$id = $data['id'];
			unset($data['id']);
			return self::dbCmsMaster()->table('game_expedition')->where('id','=',$id)->update($data);
		}else{
			if(isset($data['gid'])){
				self::doOpen($data['gid']);
			}
			$data['addtime'] = time();
			return self::dbCmsMaster()->table('game_expedition')->insertGetId($data);
		}
	}
	
	public static function deleteExpedition($gid)
	{
		self::doClose($gid);
		return self::dbCmsMaster()->table('game_expedition')->where('gid','=',$gid)->delete();
	}
}