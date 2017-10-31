<?php
namespace Yxd\Services\Cms;

use Illuminate\Support\Facades\DB;
use Yxd\Services\Service;
use Yxd\Models\Cms\Game;

class GameAskService extends Service
{
	
	public static function getAskInfo($game_id)
	{
		$now = time();
		$ask = self::dbClubSlave()->table('activity')
		->where('game_id','=',$game_id)
		->where('status','=',1)
		->where('startdate','<=',$now)
		->where('enddate','>=',$now)
		->orderBy('startdate','desc')
		->first();
		
		return $ask;
	}
}