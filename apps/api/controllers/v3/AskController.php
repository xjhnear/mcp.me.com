<?php

/**
 * 问答
 */

use Yxd\Services\Cms\GameService;
use Illuminate\Support\Facades\Input;
use Yxd\Services\AskService;
class AskController extends BaseController
{
	/**
	 * 游戏问答首页
	 */
	public function home()
	{
		$gameid = Input::get('gid');
		if(empty($gameid)) return $this->fail(1121,'游戏ID不能为空');
		$out = array();
		$result = AskService::getAskHome($gameid);
		$game = GameService::getGameInfo($gameid);
		foreach($result as $index=>$row){
			$out[$index]['qid'] = $row['tid'];
			$out[$index]['gameID'] = $row['gid'];
			$out[$index]['gameIcon'] = GameService::joinImgUrl($game['ico']);
			$out[$index]['questionStatus'] = $row['status'];
			$out[$index]['questionDate'] = date('Y-m-d H:i:s',$row['dateline']);
			$out[$index]['title'] = $row['subject'];
			$out[$index]['img'] = '';
			$out[$index]['imgWidth'] = '';
			$out[$index]['imgHeight'] = '';
			$out[$index]['userID'] = $row['author']['uid'];
			$out[$index]['userName'] = $row['author']['nickname'];
			$out[$index]['userLevel'] = $row['author']['level_name'];
			$out[$index]['award'] = $row['award'];
			$out[$index]['userAvator'] = AskService::joinImgUrl($row['author']['avatar']);
			$out[$index]['commentCount'] = $row['replies'];
		}
		$total = 10;
		return $this->success(array('result'=>$out,'totalCount'=>$total));
	}
}