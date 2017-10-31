<?php
namespace Youxiduo\Activity\Share;
use Youxiduo\Helper\Utility;

class GiftbagService extends Service
{	
	/**
	 * 保存礼包信息
	 */
	public static function saveInfo($data)
	{
		if(isset($data['id']) && $data['id']>0){
			$id = $data['id'];
			unset($data['id']);
			self::db()->table('giftbag')->where('id','=',$id)->update($data);			
		}else{
			$data['addtime'] = time();
			$id = self::db()->table('giftbag')->insertGetId($data);
		}
		return $id;
	}
	
	/**
	 * 搜索礼包
	 */
	public static function searchList($search,$pageIndex=1,$pageSize=10)
	{
		$total = self::buildSearch($search)->count();
		$result = self::buildSearch($search)->forPage($pageIndex,$pageSize)->orderBy('id','desc')->get();
		return array('result'=>$result,'totalCount'=>$total);
	}
	
	protected static function buildSearch($search)
	{
		$tb = self::db()->table('giftbag');
		
		return $tb;
	}
	
	public static function initCardNoNumber($giftbag_id)
	{
		$total_num = self::db()->table('giftbag_card')->where('giftbag_id','=',$giftbag_id)->count();
		$last_num = self::db()->table('giftbag_card')->where('giftbag_id','=',$giftbag_id)->where('is_get','=',0)->count();
		return self::db()->table('giftbag')->where('id','=',$giftbag_id)->update(array('total_num'=>$total_num,'last_num'=>$last_num));
	}
	
	/**
	 * 搜索礼包卡
	 */
	public static function searchCardNoList($search,$pageIndex=1,$pageSize=10,$sort=array())
	{
		$out = array();
		$out['totalCount'] = self::buildSearchCardNo($search)->count();
		$tb = self::buildSearchCardNo($search)->forPage($pageIndex,$pageSize);
		
		foreach($sort as $field=>$order){
			$tb = $tb->orderBy($field,$order);
		}
		$out['result'] = $tb->get();
		return $out;
	}
	
	protected static function buildSearchCardNo($search)
	{
		$tb = self::db()->table('giftbag_card');
		if(isset($search['giftbag_id'])){
			$tb = $tb->where('giftbag_id','=',$search['giftbag_id']);
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
	 * 获取礼包信息
	 */
	public static function getInfo($giftbag_id)
	{
		return self::db()->table('giftbag')->where('id','=',$giftbag_id)->first();
	}
	
	/**
	 * 获取礼包卡列表
	 */
	public static function getCardNoList($giftbag_id)
	{
		return self::db()->table('giftbag_card')->where('giftbag_id','=',$giftbag_id)->lists('cardno');
	}
	
	/**
	 * 删除卡号
	 */
	public static function deleteCardNo($id)
	{
		return self::db()->table('giftbag_card')->where('id','=',$id)->delete();
	}
	
	/**
	 * 清空卡号
	 */
	public static function clearCardNo($giftbag_id,$all=false)
	{
		$tb = self::db()->table('giftbag_card')->where('giftbag_id','=',$giftbag_id);
		if($all === false){
			$tb = $tb->where('is_get','=',0);
		}
		return $tb->delete();
	}
	
	/**
	 * 批量导入礼包卡
	 */
	public static function importCardNoList($giftbag_id,array $cards)
	{
		$result = self::db()->transaction(function()use($cards,$giftbag_id){
			$total = count($cards);
			if($total>500){
			    $batch_card = array_chunk($cards,500);
			    foreach($batch_card as $group){
			    	$table = array();
			    	foreach($group as $cardno){
			    		$table[] = array(
			    		    'giftbag_id'=>$giftbag_id,
			    		    'cardno'=>$cardno['cardno'],
			    		    'is_get'=>0,
			    		    'addtime'=>time(),
			    		    'adddate'=>$cardno['adddate']
			    		);
			    	}
			    	if($table){
			    		GiftbagService::db()->table('giftbag_card')->insert($table);
			    	}
			    }
			    //GiftbagService::db()->table('giftbag')->where('id','=',$giftbag_id)->increment('total_num',$total);
		    	//GiftbagService::db()->table('giftbag')->where('id','=',$giftbag_id)->increment('last_num',$total);
		    	GiftbagService::initCardNoNumber($giftbag_id);
			    return true;
			}else{
			    $table = array();
		    	foreach($cards as $cardno){
		    		$table[] = array(
		    		    'giftbag_id'=>$giftbag_id,
		    		    'cardno'=>$cardno['cardno'],
		    		    'is_get'=>0,
		    		    'addtime'=>time(),
		    		    'adddate'=>$cardno['adddate']
		    		);
		    	}
		    	if($table){
		    		GiftbagService::db()->table('giftbag_card')->insert($table);
		    		//GiftbagService::db()->table('giftbag')->where('id','=',$giftbag_id)->increment('total_num',$total);
		    		//GiftbagService::db()->table('giftbag')->where('id','=',$giftbag_id)->increment('last_num',$total);
		    		GiftbagService::initCardNoNumber($giftbag_id);
		    		return true;
		    	}
		    	return false;
			}
		});
	    return $result;
	}

	/**------------------ 前台 -----------------
	 * 获取有效的礼包码
	 * @param $giftbag_id
	 * @param $adddate
	 * @return
	 */
	public static function getValidGiftbagCard($giftbag_id,$adddate){
		return self::db()->table('giftbag_card')
			->where('giftbag_id',$giftbag_id)
			->where('adddate',$adddate)
			->where('is_get',0)
			->get();
	}

	/**
	 * 获取礼包类型信息
	 * @param $activity_id
	 * @return mixed
	 */
	public static function getGiftbagInfo($activity_id){
		return self::db()->table('giftbag')
			->where('activity_id',$activity_id)
			->where('is_show',1)
			->first();
	}

	/**
	 * 更新礼包状态（发礼包）
	 * @param $id
	 * @param $uid
	 * @return mixed
     */
	public static function updateGiftbagStatus($id,$uid){
		return self::db()->table('giftbag_card')
			->where('id',$id)
			->where('is_get',0)
			->update(array('is_get'=>1,'gettime'=>time(),'uid'=>$uid,'ip'=>''));
	}
}