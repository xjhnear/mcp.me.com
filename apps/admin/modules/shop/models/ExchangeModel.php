<?php
namespace modules\shop\models;

use Yxd\Services\UserService;

use Yxd\Modules\Core\BaseModel;

class ExchangeModel extends BaseModel
{
    public static function search($search,$page,$size)
	{
		$total = self::buildSearch($search)->count();
		$list = self::buildSearch($search)->forPage($page,$size)->orderBy('shop_goods_account.id','desc')->get();
		$uids = array();
		foreach($list as $row){
			$uids[] = $row['uid'];
		}
		if($uids){
			$users = UserService::getBatchUserInfo($uids);
		}
		foreach($list as $key=>$row){
			$row['user'] = isset($users[$row['uid']]) ? $users[$row['uid']] : array();
			$list[$key] = $row;
		}
		return array('list'=>$list,'total'=>$total);
	}
	
    protected static function buildSearch($search)
	{
		$tb = self::dbClubSlave()->table('shop_goods_account')->select('shop_goods_account.*','shop_goods.name','shop_goods.listpic')->leftJoin('shop_goods','shop_goods_account.goods_id','=','shop_goods.id');
		if(isset($search['goods_id']) && $search['goods_id']){
			$tb = $tb->where('shop_goods_account.goods_id','=',$search['goods_id']);
		}
		return $tb;
	}
	
	public static function update($id,$data){
		if(!$id || !$data) return false;
		return self::dbClubSlave()->table('shop_goods_account')->where('id',$id)->update($data);
	}
}