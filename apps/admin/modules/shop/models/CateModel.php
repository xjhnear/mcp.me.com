<?php
namespace modules\shop\models;

use Yxd\Modules\Core\BaseModel;

class CateModel extends BaseModel
{
	/**
	 * 
	 */
	public static function getList($page=1,$pagesize=10)
	{
		$total = self::dbClubSlave()->table('shop_cate')->count();
		
		$catelist = self::dbClubSlave()->table('shop_cate')
		->orderBy('sort','desc')
		->forPage($page,$pagesize)
		->get();
		return array('result'=>$catelist,'total'=>$total);
	}
	
	public static function getKV()
	{
		return self::dbClubSlave()->table('shop_cate')
		->orderBy('sort','desc')
		->lists('cate_name','id');
	}
	
    public static function getInfo($id)
	{
		return self::dbClubSlave()->table('shop_cate')->where('id','=',$id)->first();
	}
	
	public static function save($data)
	{
		if(isset($data['id']) && $data['id']){
			$id = $data['id'];
			unset($data['id']);
			return self::dbClubMaster()->table('shop_cate')->where('id','=',$id)->update($data);
		}else{
			return self::dbClubMaster()->table('shop_cate')->insertGetId($data);
		}
	}
}