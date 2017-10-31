<?php
namespace modules\giftbag\models;

use Yxd\Services\Cms\GameService;

use Illuminate\Support\Facades\DB;
use Yxd\Modules\Core\BaseModel;
use Yxd\Modules\Activity\GiftbagService;

class GiftCardModel extends BaseModel
{
    /**
	 * 搜索礼包卡
	 */
	public static function search($search,$pageIndex=1,$pageSize=10,$sort=array())
	{
		$out = array();
		$out['total'] = self::buildSearch($search)->count();
		$tb = self::buildSearch($search)->forPage($pageIndex,$pageSize);
		
		foreach($sort as $field=>$order){
			$tb = $tb->orderBy($field,$order);
		}
		$out['result'] = $tb->get();
		return $out;
	}
	
	protected static function buildSearch($search)
	{
		$tb = self::dbClubSlave()->table('giftbag_card');
		if(isset($search['giftbag_id'])){
			$tb = $tb->where('giftbag_id','=',$search['giftbag_id']);
		}
		if(isset($search['is_get'])){
			$tb = $tb->where('is_get','=',$search['is_get']);
		}
		return $tb;
	}
	
	public static function exportCardNo($giftbag_id,$number)
	{
		$ids = self::dbClubMaster()->table('giftbag_card')->where('giftbag_id','=',$giftbag_id)->where('is_get','=',0)->orderBy('id','desc')->forPage(1,$number)->lists('id');
		if(!$ids) return array();		
		$rows = self::dbClubMaster()->table('giftbag_card')->whereIn('id',$ids)->where('is_get','=',0)->update(array('is_get'=>1));
		$result = self::dbClubMaster()->table('giftbag_card')->where('giftbag_id','=',$giftbag_id)->whereIn('id',$ids)->where('is_get','=',1)->where('uid','=',0)->get();
		self::dbClubMaster()->table('giftbag')->where('id','=',$giftbag_id)->decrement('last_num',$rows);
		return $result;
	}
	
	public static function exportUserCardNo($giftbag_id,$is_get=null)
	{
		$search['giftbag_id'] = $giftbag_id;
		if($is_get !== null) $search['is_get'] = (int)$is_get;
		$tb = self::dbClubSlave()->table('giftbag_card');
		if(isset($search['giftbag_id'])){
			$tb = $tb->where('giftbag_id','=',$search['giftbag_id']);
		}
		if(isset($search['is_get'])){
			$tb = $tb->where('is_get','=',$search['is_get']);
		}
		$result = $tb->orderBy('gettime','asc')->get();
		return $result;
	}
	
	/**
	 * 获取礼包卡号数组
	 */
	public static function getCardNoList($giftbag_id)
	{
		return self::dbClubSlave()->table('giftbag_card')->where('giftbag_id','=',$giftbag_id)->lists('cardno');
	}
	
	public static function delete($id)
	{
		$giftbag = self::dbClubMaster()->table('giftbag_card')->where('id','=',$id)->first();
		$result = self::dbClubMaster()->table('giftbag_card')->where('id','=',$id)->delete();
		if($giftbag){
			self::dbClubMaster()->table('giftbag')->where('id','=',$giftbag['giftbag_id'])->decrement('total_num');
			self::dbClubMaster()->table('giftbag')->where('id','=',$giftbag['giftbag_id'])->decrement('last_num');
		    GiftbagService::initGiftbagCardNoQueue($giftbag['giftbag_id']);
		}
		return $result;
	}
	
	public static function getReport($startdate,$enddate,$pageIndex=1,$pageSize=10)
	{
		$all_total_count = self::dbClubMaster()->table('giftbag_card')
		    ->where('is_get','=',1)
		    ->where('gettime','>=',$startdate)
			->where('gettime','<=',$enddate)
			->count();
		$total_row = self::dbClubMaster()->table('giftbag_card')
		    ->where('is_get','=',1)
		    ->where('gettime','>=',$startdate)
			->where('gettime','<=',$enddate)
			->groupBy('giftbag_id')
			->get();
		$total = count($total_row);
		if($total==0) return array('result'=>array(),'total'=>0,'total_count'=>0);
		//领取量
		$out_result = self::dbClubMaster()->table('giftbag_card')
			->select(DB::Raw('giftbag_id,count(*) as total'))
			->where('is_get','=',1)
			->where('gettime','>=',$startdate)
			->where('gettime','<=',$enddate)
			->groupBy('giftbag_id')
			->forPage($pageIndex,$pageSize)
			->orderBy('total','desc')
			->lists('total','giftbag_id');
		if(!$out_result) return array();
		$ids = array_keys($out_result);
		//入库量
		$in_result = self::dbClubMaster()->table('giftbag_card')
			->select(DB::Raw('giftbag_id,count(*) as total'))
			->whereIn('giftbag_id',$ids)
			->where('ctime','>=',$startdate)
			->where('ctime','<=',$enddate)	
			->groupBy('giftbag_id')		
			->orderBy('total','desc')
			->lists('total','giftbag_id');
		//总量
		$total_result = self::dbClubMaster()->table('giftbag_card')
			->select(DB::Raw('giftbag_id,count(*) as total'))
			->whereIn('giftbag_id',$ids)	
			->groupBy('giftbag_id')		
			->orderBy('total','desc')
			->lists('total','giftbag_id');
		$giftbags = self::dbClubMaster()->table('giftbag')
		    ->select()->whereIn('id',$ids)
		    ->lists('title','id');
		$tmp = self::dbClubMaster()->table('giftbag')
		    ->select('game_id','id')->whereIn('id',$ids)
		    ->lists('game_id','id');
		$gids = array_unique(array_values($tmp));
		$games = GameService::getGamesByIds($gids);    
		$out = array();
		foreach($out_result as $key=>$val){
			$out[$key] = array(
			    'giftbag_id'=>$key,
			    'title'=>$giftbags[$key],
			    'gname'=>isset($games[$tmp[$key]]['shortgname']) ? $games[$tmp[$key]]['shortgname'] : '',
			    'out_count'=>$val,
			    'in_count'=>(isset($in_result[$key]) ? $in_result[$key] : 0),
			    'total_count'=>(isset($total_result[$key]) ? $total_result[$key] : 0)
			);
		}
		return array('result'=>$out,'total'=>$total,'total_count'=>$all_total_count);
	}
	
	/**
	 * 导入礼包卡
	 */
	public static function importCardNo($cards,$giftbag_id)
	{
		$result = self::dbClubMaster()->transaction(function()use($cards,$giftbag_id){
			$total = count($cards);
			if($total>500){
			    $batch_card = array_chunk($cards,500);
			    foreach($batch_card as $group){
			    	$table = array();
			    	foreach($group as $cardno){
			    		$table[] = array(
			    		    'giftbag_id'=>$giftbag_id,
			    		    'cardno'=>$cardno,
			    		    'is_get'=>0,
			    		    'ctime'=>time()
			    		);
			    	}
			    	if($table){
			    		GiftCardModel::dbClubMaster()->table('giftbag_card')->insert($table);
			    	}
			    }
			    GiftCardModel::dbClubMaster()->table('giftbag')->where('id','=',$giftbag_id)->increment('total_num',$total);
		    	GiftCardModel::dbClubMaster()->table('giftbag')->where('id','=',$giftbag_id)->increment('last_num',$total);
			    return true;
			}else{
			    $table = array();
		    	foreach($cards as $cardno){
		    		$table[] = array(
		    		    'giftbag_id'=>$giftbag_id,
		    		    'cardno'=>$cardno,
		    		    'is_get'=>0,
		    		    'ctime'=>time()
		    		);
		    	}
		    	if($table){
		    		GiftCardModel::dbClubMaster()->table('giftbag_card')->insert($table);
		    		GiftCardModel::dbClubMaster()->table('giftbag')->where('id','=',$giftbag_id)->increment('total_num',$total);
		    		GiftCardModel::dbClubMaster()->table('giftbag')->where('id','=',$giftbag_id)->increment('last_num',$total);
		    		return true;
		    	}
		    	return false;
			}
		});
		if($result){
			GiftbagService::initGiftbagCardNoQueue($giftbag_id);
		}
	    return $result;
	}
}