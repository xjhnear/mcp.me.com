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
use Youxiduo\Android\GiftbagService;
/**
 * 游戏礼包模型类
 */
final class GiftbagCard extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}	
	
    /**
	 * 搜索礼包卡
	 */
	public static function m_search($search,$pageIndex=1,$pageSize=10,$sort=array())
	{
		$out = array();
		$out['total'] = self::m_buildSearch($search)->count();
		$tb = self::m_buildSearch($search)->forPage($pageIndex,$pageSize);
		
		foreach($sort as $field=>$order){
			$tb = $tb->orderBy($field,$order);
		}
		$out['result'] = $tb->get();
		return $out;
	}
	
	public static function getUsableCardList($giftbag_id)
	{
		return self::db()->where('giftbag_id','=',$giftbag_id)->where('is_get','=',0)->get();
	}
	
    public static function updateGiftbagCardStatus($id,$uid=0,$ip='')
	{
		return self::db()->where('id','=',$id)->where('is_get','=',0)->update(array('is_get'=>1,'gettime'=>time(),'uid'=>$uid,'ip'=>$ip,'lock_uid'=>$uid));
	}
	
	protected static function m_buildSearch($search)
	{
		$tb = self::db();
		
		if(isset($search['giftbag_id']) && $search['giftbag_id']){
			$tb = $tb->where('giftbag_id','=',$search['giftbag_id']);
		}
		
	    if(isset($search['uid']) && $search['uid']){
			$tb = $tb->where('uid','=',$search['uid']);
		}
		
		if(isset($search['is_get'])){
			$tb = $tb->where('is_get','=',$search['is_get']);
		}
		
	    //开始时间
		if(isset($search['startdate']) && !empty($search['startdate']))
		{
			$tb = $tb->where('gettime','>=',strtotime($search['startdate'] . ' 00:00:00'));
		}
		//截至时间
		if(isset($search['enddate']) && !empty($search['enddate']))
		{
			$tb = $tb->where('gettime','<=',strtotime($search['enddate'] . ' 23:59:59'));
		}
		
		return $tb;
	}
	
	/**
	 * 获取礼包卡号数组
	 */
	public static function m_getCardNoList($giftbag_id)
	{
		return self::db()->where('giftbag_id','=',$giftbag_id)->lists('cardno');
	}
	
	public static function m_delete($id)
	{
		$giftbag = self::db()->where('id','=',$id)->first();
		$result = self::db()->where('id','=',$id)->delete();
		if($giftbag){
		    GiftbagService::initGiftbagCardNoQueue($giftbag['giftbag_id']);
		    $total = self::db()->where('giftbag_id','=',$giftbag['giftbag_id'])->count();
		    $last  = self::db()->where('giftbag_id','=',$giftbag['giftbag_id'])->where('is_get','=',0)->count();
		    Giftbag::db()->where('id','=',$giftbag['giftbag_id'])->update(array('total_num'=>$total,'last_num'=>$last));
		}
		return $result;
	}
	
	public static function m_clear($giftbag_id)
	{
		$result = self::db()->where('giftbag_id','=',$giftbag_id)->delete();
		if($result){
		    GiftbagService::initGiftbagCardNoQueue($giftbag_id);
		    $total = self::db()->where('giftbag_id','=',$giftbag_id)->count();
		    $last  = self::db()->where('giftbag_id','=',$giftbag_id)->where('is_get','=',0)->count();
		    Giftbag::db()->where('id','=',$giftbag_id)->update(array('total_num'=>$total,'last_num'=>$last));
		}
		return $result;
	}
	
	/**
	 * 导入礼包卡
	 */
	public static function m_importCardNo($cards,$giftbag_id)
	{
		$result = GiftbagCard::transaction(function()use($cards,$giftbag_id){
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
			    		GiftbagCard::db()->insert($table);
			    	}
			    }
			    Giftbag::db()->where('id','=',$giftbag_id)->increment('total_num',$total);
		    	Giftbag::db()->where('id','=',$giftbag_id)->increment('last_num',$total);
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
		    		GiftbagCard::db()->insert($table);
		    		Giftbag::db()->where('id','=',$giftbag_id)->increment('total_num',$total);
		    		Giftbag::db()->where('id','=',$giftbag_id)->increment('last_num',$total);
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
	
    public static function exportCardNo($giftbag_id,$number)
	{
		$ids = self::db()->where('giftbag_id','=',$giftbag_id)->where('is_get','=',0)->orderBy('id','desc')->forPage(1,$number)->lists('id');
		if(!$ids) return array();		
		$rows = self::db()->whereIn('id',$ids)->where('is_get','=',0)->update(array('is_get'=>1));
		$result = self::db()->where('giftbag_id','=',$giftbag_id)->whereIn('id',$ids)->where('is_get','=',1)->where('uid','=',0)->get();
		Giftbag::db()->where('id','=',$giftbag_id)->decrement('last_num',$rows);
		return $result;
	}
	
	protected static function buildReport($gfid=array())
	{
		$tb = self::db();
		if($gfid){
			$tb = $tb->whereIn('giftbag_id',$gfid);
		}
		return $tb;
	}
	
    public static function getReport($startdate,$enddate,$pageIndex=1,$pageSize=10,$gfid=array())
	{
		$all_total_count = self::buildReport($gfid)
		    ->where('is_get','=',1)
		    ->where('gettime','>=',$startdate)
			->where('gettime','<=',$enddate)
			->count();
		$total_row = self::buildReport($gfid)
		    ->where('is_get','=',1)
		    ->where('gettime','>=',$startdate)
			->where('gettime','<=',$enddate)
			->groupBy('giftbag_id')
			->get();
		$total = count($total_row);
		if($total==0) return array('result'=>array(),'total'=>0,'total_count'=>0);
		//领取量
		$out_result = self::buildReport($gfid)
			->select(self::Raw('giftbag_id,count(*) as total'))
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
		$in_result = self::buildReport($gfid)
			->select(self::Raw('giftbag_id,count(*) as total'))
			->whereIn('giftbag_id',$ids)
			->where('ctime','>=',$startdate)
			->where('ctime','<=',$enddate)	
			->groupBy('giftbag_id')		
			->orderBy('total','desc')
			->lists('total','giftbag_id');
		//总量
		$total_result = self::buildReport($gfid)
			->select(self::Raw('giftbag_id,count(*) as total'))
			->whereIn('giftbag_id',$ids)	
			->groupBy('giftbag_id')		
			->orderBy('total','desc')
			->lists('total','giftbag_id');
		$giftbags = Giftbag::db()
		    ->select()->whereIn('id',$ids)
		    ->lists('title','id');
		$tmp = Giftbag::db()
		    ->select('game_id','id')->whereIn('id',$ids)
		    ->lists('game_id','id');
		$gids = array_unique(array_values($tmp));
		$games = Game::getListByIds($gids);
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
}