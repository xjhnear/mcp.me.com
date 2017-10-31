<?php
namespace modules\forum\models;

use Yxd\Modules\Core\BaseModel;
class LikeModel extends BaseModel
{
	public static function getLikeList($target_id, $topic, $pageindex=1, $pagesize=10)
	{
		$topic_likes = self::dbClubSlave()->table('like')->where('target_id','=',$target_id)->where('target_table', $topic)->select('uid', 'ctime')->orderBy('ctime', 'desc')->forPage($pageindex, $pagesize)->get();
		$total = self::dbClubSlave()->table('like')->where('target_id','=',$target_id)->where('target_table', $topic)->count();
		$result=array("total"=>$total, "topic_likes"=>$topic_likes);
		return $result;
	}	
}