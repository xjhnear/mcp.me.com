<?php
namespace Youxiduo\Activity\Share;

class RechargeService extends Service
{
	const TB_PROXY_RECHARGE = 'proxy_recharge';
	const TB_PROXY_RECHARGE_PRICE = 'proxy_recharge_price';
	
	public static function saveInfo($data)
	{
		if(isset($data['id']) && $data['id']>0){
			$id = $data['id'];
			unset($data['id']);
			self::db()->table(self::TB_PROXY_RECHARGE)->where('id','=',$id)->update($data);			
		}else{
			$data['addtime'] = time();
			$id = self::db()->table(self::TB_PROXY_RECHARGE)->insertGetId($data);
		}
		return $id;
	}
	
    /**
	 * 搜索代充产品
	 */
	public static function searchList($search,$pageIndex=1,$pageSize=10)
	{
		$total = self::buildSearch($search)->count();
		$result = self::buildSearch($search)->forPage($pageIndex,$pageSize)->orderBy('id','desc')->get();
		return array('result'=>$result,'totalCount'=>$total);
	}
	
	protected static function buildSearch($search)
	{
		$tb = self::db()->table(self::TB_PROXY_RECHARGE);
		
		return $tb;
	}
	
    /**
	 * 获取产品信息
	 */
	public static function getInfo($recharge_id)
	{
		return self::db()->table(self::TB_PROXY_RECHARGE)->where('id','=',$recharge_id)->first();
	}
	
	
    /**
	 * 搜索价格
	 */
	public static function searchPriceList($search,$pageIndex=1,$pageSize=10,$sort=array())
	{
		$out = array();
		$out['totalCount'] = self::buildSearchPrice($search)->count();
		$tb = self::buildSearchPrice($search)->forPage($pageIndex,$pageSize);
		
		foreach($sort as $field=>$order){
			$tb = $tb->orderBy($field,$order);
		}
		$out['result'] = $tb->get();
		return $out;
	}
	
	protected static function buildSearchPrice($search)
	{
		$tb = self::db()->table(self::TB_PROXY_RECHARGE_PRICE);
		if(isset($search['proxy_id'])){
			$tb = $tb->where('proxy_id','=',$search['proxy_id']);
		}		
		return $tb;
	}
	
    /**
	 * 获取价格信息
	 */
	public static function getPriceInfo($id)
	{
		return self::db()->table(self::TB_PROXY_RECHARGE_PRICE)->where('id','=',$id)->first();
	}
	
	public static function savePriceInfo($data)
	{
		if(isset($data['id']) && $data['id']>0){
			$id = $data['id'];
			unset($data['id']);
			self::db()->table(self::TB_PROXY_RECHARGE_PRICE)->where('id','=',$id)->update($data);			
		}else{
			$id = self::db()->table(self::TB_PROXY_RECHARGE_PRICE)->insertGetId($data);
		}
		return $id;
	}
	
    /**
	 * 获取价格列表
	 */
	public static function getPriceList($proxy_id)
	{
		return self::db()->table(self::TB_PROXY_RECHARGE_PRICE)->where('proxy_id','=',$proxy_id)->orderBy('price','asc')->lists('price');
	}
	
	/**
	 * 删除价格
	 */
	public static function deletePrice($id)
	{
		return self::db()->table(self::TB_PROXY_RECHARGE_PRICE)->where('id','=',$id)->delete();
	}
	
	/**
	 * 清空价格
	 */
	public static function clearPrice($proxy_id,$all=false)
	{
		$tb = self::db()->table(self::TB_PROXY_RECHARGE_PRICE)->where('proxy_id','=',$proxy_id);
		return $tb->delete();
	}
}