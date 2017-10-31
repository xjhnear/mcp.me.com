<?php

namespace modules\adv\models;
use Yxd\Services\Cms\GameService;

use Yxd\Modules\Core\BaseModel;

class GameCreditModel extends BaseModel
{
	public static function search($search,$page=1,$size=10)
	{
		$total = self::buildSearch($search)->count();
		$list = self::buildSearch($search)->forPage($page,$size)->orderBy('addtime','desc')->get();
		$game_ids = array();
		foreach($list as $row){
			$game_ids[] = $row['game_id'];
		}
		$game_ids = array_unique($game_ids);
		$games = GameService::getGamesByIds($game_ids);
		foreach($list as $key=>$row){
			$row['game'] = isset($games[$row['game_id']]) ? $games[$row['game_id']] : array();
			$list[$key] = $row;
		}
		return $list;
	}
	
	protected static function buildSearch($search)
	{
		return self::dbClubSlave()->table('game_credit');
	}
	
	public static function getInfo($id)
	{
		$info = self::dbClubSlave()->table('game_credit')->where('id','=',$id)->first();
		$info['game'] = GameService::getGameInfo($info['game_id']);
		return $info;
	}
	
	public static function delete($id)
	{
		return self::dbClubSlave()->table('game_credit')->where('id','=',$id)->delete();
	}
	
	public static function save($data)
	{		
		if(isset($data['id']) && $data['id']){
			$id = $data['id'];
			unset($data['id']);
			return self::dbClubMaster()->table('game_credit')->where('id','=',$id)->update($data);
		}else{
			$data['addtime'] = time();
			return self::dbClubMaster()->table('game_credit')->insertGetId($data);
		}
	}
}