<?php

namespace modules\giftbag\models;
use Yxd\Services\Cms\GameService;

use Yxd\Modules\Core\BaseModel;

class GiftbagModel extends BaseModel
{
	/**
	 * 保存礼包信息
	 */
	public static function save($data)
	{
		if(isset($data['id']) && $data['id']>0){
			$id = $data['id'];
			unset($data['id']);
			self::dbClubMaster()->table('giftbag')->where('id','=',$id)->update($data);
			if(isset($data['mutex_giftbag_id'])){
				if($data['mutex_giftbag_id']>0){
					self::dbClubMaster()->table('giftbag')->where('id','=',$data['mutex_giftbag_id'])->update(array('mutex_giftbag_id'=>$id));
				}else{
					self::dbClubMaster()->table('giftbag')->where('mutex_giftbag_id','=',$id)->update(array('mutex_giftbag_id'=>0));
				}
			}

		}else{
			$data['ctime'] = time();
			$id = self::dbClubMaster()->table('giftbag')->insertGetId($data);
			if(isset($data['mutex_giftbag_id'])){
				if($data['mutex_giftbag_id']>0){
					self::dbClubMaster()->table('giftbag')->where('id','=',$data['mutex_giftbag_id'])->update(array('mutex_giftbag_id'=>$id));
				}else{
					self::dbClubMaster()->table('giftbag')->where('mutex_giftbag_id','=',$id)->update(array('mutex_giftbag_id'=>0));
				}
			}
		}
		return $id;
	}
	
	/**
	 * 搜索礼包
	 */
	public static function search($search,$pageIndex=1,$pageSize=10,$sort=array())
	{
		$out = array();
		$out['total'] = self::buildSearch($search)->count();
		$tb = self::buildSearch($search)->forPage($pageIndex,$pageSize);
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
			$games = GameService::getGamesByIds($game_ids);
		}
		foreach($result as $key=>$row){
			$row['game'] = isset($games[$row['game_id']]) ? $games[$row['game_id']] : array();
			$result[$key] = $row;
		}
		$out['result'] = $result;
		return $out;
	}
	
	protected static function buildSearch($search)
	{
		$tb = self::dbClubSlave()->table('giftbag');
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
		return $tb;
	}
	
	/**
	 * 礼包
	 */
	public static function getInfoByIds($ids)
	{
		if(!$ids) return array();
		$list = self::dbClubSlave()->table('giftbag')->whereIn('id',$ids)->get();
		$data = array();
		foreach($list as $row){
			$data[$row['id']] = $row;
		}
		return $data;
	}
	
	/**
	 * 获取礼包信息
	 */
	public static function getInfo($id)
	{
		$info = self::dbClubSlave()->table('giftbag')->where('id','=',$id)->first();
		$info['condition'] = json_decode($info['condition'],true);
		return $info;
	}	
	
	/**
	 * 获取礼包指定发放用户ID
	 * @param int $giftbag_id
	 * @return array
	 */
	public static function getGiftbagAppointUids($giftbag_id){
		if(!$giftbag_id) return array();
		return self::dbClubSlave()->table('giftbag_appoint')->where('giftbag_id',$giftbag_id)->lists('uid');
	}
	
	/**
	 * 更新礼包指定发放用户ID
	 * @param int $giftbag_id
	 * @param arr $uids
	 */
	public static function updateGiftbagAppointUids($giftbag_id,$data){
		$exception = self::dbClubMaster()->transaction(function() use ($giftbag_id,$data){
			//删除礼包原先指定的所有用户id
			GiftbagModel::dbClubSlave()->table('giftbag_appoint')->where('giftbag_id',$giftbag_id)->delete();
			GiftbagModel::dbClubSlave()->table('giftbag_appoint')->insert($data);
		});
		return is_null($exception) ? true : false;
	}
	
	/**
	 * 通过礼包和用户id获取指定信息（判断是否指定）
	 * @param unknown $giftbag_id
	 * @param unknown $uid
	 */
	public static function getGiftbagAppointInfoByGbidAndUid($giftbag_id,$uid){
		if(!$giftbag_id || $uid) return array();
		return self::dbClubSlave()->table('giftbag_appoint')->where('giftbag_id',$giftbag_id)->where('uid',$uid)->first();
	}
}