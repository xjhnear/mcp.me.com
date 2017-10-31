<?php
use Yxd\Services\Cms\ActivityService;
use Yxd\Services\Cms\GameService;
use Illuminate\Support\Facades\Input;



class ActivityController extends BaseController
{
	/**
	 * 活动列表
	 */
	public function getList()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$gid = Input::get('gid',0);
		$out = array();
		$out['doingList'] = array();
		$out['overList'] = array();
		if($page==1){
			$doing = ActivityService::getDoingList($gid);
			
			foreach($doing['result'] as $row){
				$ask = array();
				$ask['atid'] = $row['id'];
				$ask['url'] = ActivityService::joinImgUrl($row['bigpic']);
				$ask['title'] = $row['title'];
				$ask['startTime'] = date('Y-m-d H:i:s',$row['startdate']);
				$ask['endTime'] = date('Y-m-d H:i:s',$row['enddate']);
				$ask['type'] = $row['type'];//活动类型
				$ask['tid'] = $row['rule_id'];
				$out['doingList'][] = $ask;
			}
		}else{
			$out['doingList'] = array();
		}
		$over = ActivityService::getOverList($gid,$page,$pagesize);
	    foreach($over['result'] as $row){
			$ask = array();
			$ask['atid'] = $row['id'];
			$ask['url'] = ActivityService::joinImgUrl($row['listpic']);
			$ask['title'] = $row['title'];
			$ask['startTime'] = date('Y-m-d H:i:s',$row['startdate']);
			$ask['endTime'] = date('Y-m-d H:i:s',$row['enddate']);
			$ask['type'] = $row['type'];//活动类型
			$ask['tid'] = $row['rule_id'];
			$out['overList'][] = $ask;
		}
		return $this->success(array('result'=>$out,'totalCount'=>$over['total']));
	}
	
	/**
	 * 有奖问答详情
	 */
	public function AskDetail()
	{
		$atid = Input::get('atid');
		$uid = Input::get('uid');
		
		$ask = ActivityService::getAskDetail($atid,$uid);
		if(!$ask) return $this->fail(11211,'活动不存在');
		$game = GameService::getGameInfo($ask['game_id']);
				
		$out['atid'] = $ask['id'];
		$out['title'] = $ask['title'];
		$out['img'] = ActivityService::joinImgUrl($ask['bigpic']);
		$out['startTime'] = date('Y-m-d H:i:s',$ask['startdate']);
		$out['endTime'] = date('Y-m-d H:i:s',$ask['enddate']);
		$out['winningTime'] = $ask['lotterytime'] ? date('Y-m-d H:i:s',$ask['lotterytime']) : '';
		//规则
		$out['ruleurl'] = $ask['rule_id'];
		$out['prizes'] = array();

		$out['prizes'][] = array('pName'=>'一等奖','content'=>$ask['prizes']['prize_1']['prize_name'],'image'=>$ask['prizes']['prize_1']['pic'],'num'=>$ask['prizes']['prize_1']['num']);
		$out['prizes'][] = array('pName'=>'二等奖','content'=>$ask['prizes']['prize_2']['prize_name'],'image'=>$ask['prizes']['prize_2']['pic'],'num'=>$ask['prizes']['prize_2']['num']);
		$out['prizes'][] = array('pName'=>'三等奖','content'=>$ask['prizes']['prize_3']['prize_name'],'image'=>$ask['prizes']['prize_3']['pic'],'num'=>$ask['prizes']['prize_3']['num']);
		
		$out['currentGame'] = array();
		$out['currentGame']['gid'] = $game['id'];
		$out['currentGame']['gameIcon'] = ActivityService::joinImgUrl($game['ico']);
		$out['currentGame']['title'] = $game['shortgname'];
		$out['currentGame']['subtitle'] = $game['editorcomt'];
		$out['currentGame']['downloadurl'] = $game['downurl'];
		//参与用户
		$out['attended'] = array();
		$users = $ask['attendUsers'];
		foreach($users as $row){
			$user['uid'] = $row['uid'];
			$user['avatarUrl'] = ActivityService::joinImgUrl($row['avatar']);
			$out['attended'][] = $user;
		}		
		$out['attendNum'] = $ask['attendCount'];
		
		//是否已经参加
		$out['hasAttended'] = $ask['hasAttended'];
		//中奖名单URL
		$out['listurl'] = $ask['rule_id'];
		
		$out['activitySubjects'] = array();
		$questions = $ask['questions'];
		foreach($questions as $row){
			$example = array();
			$example['numid'] = $row['id'];
			$example['content'] = $row['title'];
			$options = json_decode($row['options'],true);
			
			$example['choice'][] = array(
			    'key'=>'A',
			    'value'=>$options['option_a']
			);
			$example['choice'][] = array(
			    'key'=>'B',
			    'value'=>$options['option_b']
			);
			if($options['option_c']){
				$example['choice'][] = array(
				    'key'=>'C',
				    'value'=>$options['option_c']
				);
			}
			if($options['option_d']){
				$example['choice'][] = array(
				    'key'=>'D',
				    'value'=>$options['option_d']
				);
			}
			$out['activitySubjects'][] = $example;
		}
		
		return $this->success(array('result'=>$out));
	}
	
	/**
	 * 提交回答
	 */
	public function doCommit()
	{
		$uid = Input::get('uid');
		$atid = Input::get('atid');
		$answer = json_decode(Input::get('answer'),true);
		$result = ActivityService::doCommit($uid, $atid, $answer);
		if($result === -1){
			return $this->fail('11211','答题不完整');
		}
		if($result === false){
			return $this->fail('11211','已经提交过答案');
		}
		return $this->success(array('result'=>null));
	}
}