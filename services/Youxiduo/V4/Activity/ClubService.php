<?php
/**
 * @package Youxiduo
 * @category Base 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\V4\Activity;

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

use Youxiduo\V4\Activity\Model\Club;
use Youxiduo\V4\Activity\Model\ClubGame;

class ClubService extends BaseService
{
	public static function getClubOutInfo($ename)
	{
		$club = Club::db()->where('ename','=',$ename)->first();
		if(!$club) return false;
		$games = ClubGame::db()->where('club_id','=',$club['id'])->where('is_show','=',1)->orderBy('sort','desc')->get();
		
		$out = array();
		$out['notice'] = $club['prompt'];
		$out['name'] = $club['name'];
		$out['qq'] = $club['qq'];
		$out['comqq'] = $club['comqq'];
		$out['games'] = array();
		if($games){
			foreach($games as $row){
				$game = array(
				    'game_id'=>$row['id'],
				    'game_name'=>$row['game_name'],
				    'game_icon'=>Utility::getImageUrl($row['game_icon']),
				    'list_pic'=>Utility::getImageUrl($row['list_pic']),
				    'rebate'=>$row['rebate_info'],
				    'download_url'=>$row['download_url']
				);
				$out['games'][] = $game;
			}
		}
		return $out;
	}
}