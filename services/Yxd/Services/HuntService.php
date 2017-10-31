<?php
namespace Yxd\Services;

use Yxd\Models\PrizeModel;

use Yxd\Services\Cms\GameService;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Facades\Event;
use Yxd\Services\Models\ActivityHunt;
use Yxd\Services\Models\ActivityHuntAccount;

class HuntService extends Service
{
	/**
	 * 寻宝箱首页
	 */
	public static function homePage()
	{
		//$start = mktime(0,0,0,date('m'),date('d'),date('Y'));
		//$end = mktime(23,59,59,date('m'),date('d'),date('Y'));
		$time = time();
		$hunt = ActivityHunt::db()
		->where('status','=',1)
		->where('startdate','<=',$time)
		->where('enddate','>=',$time)
		->first();
		if(!$hunt) return array();
		$hunt_id = $hunt['id'];
		//奖品
		$hunt['first_prize'] = json_decode($hunt['first_prize'],true);
		$hunt['second_prize'] = json_decode($hunt['second_prize'],true);
		$hunt['third_prize'] = json_decode($hunt['third_prize'],true);
		//获奖情况
		$reward_count = ActivityHuntAccount::db()->select(DB::raw('reward_no,count(*) as total'))->where('hunt_id','=',$hunt_id)->groupBy('reward_no')->lists('total','reward_no');
		$last_reward_users = ActivityHuntAccount::db()->where('hunt_id','=',$hunt_id)->where('reward_no','>',0)->orderBy('addtime','desc')->forPage(1,3)->get();
		$uids = array();
		$reward_users = array();
		foreach($last_reward_users as $row){
			$uids[] = $row['uid'];
			$reward_no = (int)$row['reward_no'];
			$info = '';
			if($reward_no===1){				
				if($row['reward_score']){
					$info .= $info . $row['reward_score'] . '游币';
				}else{
					$info = $hunt['first_prize']['prize_name'];
				}
			}elseif($reward_no === 2){
				if($row['reward_score']){
					$info .= $info . $row['reward_score'] . '游币';
				}else{
				    $info = $hunt['second_prize']['prize_name'];
				}
			}elseif($reward_no === 3){
				if($row['reward_score']){
					$info .= $info . $row['reward_score'] . '游币';
				}else{
				    $info = $hunt['third_prize']['prize_name'];
				}
			}
			$reward_users[$row['uid']] = $info;
		}
		$users = array();
		$users = UserService::getBatchUserInfo($uids);
		
		$ids = array($hunt['first_prize']['prize_id'],$hunt['second_prize']['prize_id'],$hunt['third_prize']['prize_id']);
		$prizes = PrizeModel::getList($ids);
		$out = array();
		$out['atid'] = $hunt['id'];
		$out['tid'] = $hunt['rule_id'];
		$out['prizeImgOne'] = self::joinImgUrl($prizes[$hunt['first_prize']['prize_id']]['listpic']);
		$out['oneRemain'] = isset($reward_count[1]) ? ($hunt['first_prize']['num']-$reward_count[1]) : $hunt['first_prize']['num'];
		$out['prizeImgTwo'] = self::joinImgUrl($prizes[$hunt['second_prize']['prize_id']]['listpic']);
		$out['twoRemain'] = isset($reward_count[2]) ? ($hunt['second_prize']['num']-$reward_count[2]) : $hunt['second_prize']['num'];
		$out['prizeImgThr'] = self::joinImgUrl($prizes[$hunt['third_prize']['prize_id']]['listpic']);
		$out['thrRemain'] = isset($reward_count[3]) ? ($hunt['third_prize']['num']-$reward_count[3]) : $hunt['third_prize']['num'];
		$out['nameList'] = array();
		foreach($users as $row){
			$user = array();
			$user['nick'] = $row['nickname'];
			$user['avatar'] = self::joinImgUrl($row['avatar']);
			$user['getCoin'] = $reward_users[$row['uid']];
			$out['nameList'][] = $user;
		}
		$game = GameService::getGameInfo($hunt['game_id']);
		//$out['game'] = array();
		$out['gameimg'] = self::joinImgUrl($game['ico']);
		$out['gid'] = $game['id'];
		$out['gamename'] = $game['shortgname'];
		$out['gamecontent'] = $game['typename'] . ' | '  . $game['language'];
		return $out;
	}
	
	public static function doHunt()
	{
		
	}
}

