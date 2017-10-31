<?php
namespace Youxiduo\Activity\Share;

class GoodsService extends Service
{
	const TB_GOODS = 'goods';
	
    public static function saveInfo($data)
	{
		if(isset($data['id']) && $data['id']>0){
			$id = $data['id'];
			unset($data['id']);
			$data['updatetime'] = time();
			self::db()->table(self::TB_GOODS)->where('id','=',$id)->update($data);			
		}else{
			$data['addtime'] = time();
			$data['last_num'] = $data['total_num'];
			$id = self::db()->table(self::TB_GOODS)->insertGetId($data);
		}
		return $id;
	}
	
    /**
	 * 搜索产品
	 */
	public static function searchList($search,$pageIndex=1,$pageSize=10)
	{
		$total = self::buildSearch($search)->count();
		$result = self::buildSearch($search)->forPage($pageIndex,$pageSize)->orderBy('id','desc')->get();
		return array('result'=>$result,'totalCount'=>$total);
	}
	
	protected static function buildSearch($search)
	{
		$tb = self::db()->table(self::TB_GOODS);
		
		return $tb;
	}
	
    /**
	 * 获取产品信息
	 */
	public static function getInfo($goods_id)
	{
		return self::db()->table(self::TB_GOODS)->where('id','=',$goods_id)->first();
	}
}