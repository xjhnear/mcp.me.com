<?php
namespace Yxd\Models;

use Yxd\Modules\Core\BaseModel;

class PrizeModel extends BaseModel
{
	public static function getList($ids)
	{
		if(!$ids) return array();
		$list = self::dbClubSlave()->table('activity_prize')->whereIn('id',$ids)->get();
		$data = array();
		foreach($list as $row){
			$data[$row['id']] = $row;
		}
		return $data;
	}
}