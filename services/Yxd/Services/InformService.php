<?php
namespace Yxd\Services;

use Yxd\Services\Service;
use Illuminate\Support\Facades\DB;
use Yxd\Services\Models\Inform;
/**
 * 举报
 */
class InformService extends Service
{
	const INFORM_TYPE_TOPIC = 1;
	const INFORM_TYPE_REPLY = 2;
	const INFORM_TYPE_COMMENT = 3;
	
	/**
	 * 举报主题
	 */
	public static function reportTopic($tid,$uid)
	{
		$data = array(
		    'target_id'=>$tid,
		    'type'=>self::INFORM_TYPE_TOPIC,
		    'uid'=>$uid,
		    'num'=>0,
		    'addtime'=>time()
		);
		return Inform::db()->insertGetId($data);
	}
	
	/**
	 * 
	 */
	public static function reportComment($id,$typeID,$uid)
	{
		$type = null;
		switch($typeID){
			case 0:
			case 1:
			case 2:
			case 3:
			case 4:
			case 5:
			case 6:
				$type = self::INFORM_TYPE_REPLY;
				break;
		}
		if($type===null) return false;
		$data = array(
		    'target_id'=>$id,
		    'type'=>$type,
		    'uid'=>$uid,
		    'num'=>0,
		    'addtime'=>time()
		);
		return Inform::db()->insertGetId($data);
	}
}