<?php
namespace modules\activity\controllers;
use modules\activity\models\PrizeModel;

use modules\activity\models\HuntModel;

use Yxd\Services\Cms\GameService;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\Core\BackendController;
use modules\forum\models\ChannelModel;
use modules\forum\models\TopicModel;

class HuntController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'activity';
	}
	
	public function getList()
	{
		$search = array();
		$page = Input::get('page',1);
		$pagesize = 10;
		$data = array();
		$hunts = HuntModel::searchList($search,$page,$pagesize);
		$game_ids = array();
		foreach($hunts as $key=>$row){
			$game_ids[] = $row['game_id'];
			$hunts[$key]['first_prize'] = json_decode($row['first_prize']);
			$hunts[$key]['second_prize'] = json_decode($row['second_prize']);
			$hunts[$key]['third_prize'] = json_decode($row['third_prize']);
		}
		if($game_ids){
		$games = GameService::getGamesByIds($game_ids);
			foreach($games as $key=>$row){
				$row['ico'] = GameService::joinImgUrl($row['ico']);
				$games[$key] = $row;
			}
		}else{
			$games = array();
		}
		$data['hunts'] = $hunts;
		$data['games'] = $games;
		return $this->display('hunt-list',$data);
	}
	
	public function getAdd()
	{
		$data = array();
		$game_id = Input::get('game_id',0);
		if($game_id){
			$data['game'] = GameService::getGameInfo($game_id);
		}
		return $this->display('hunt-info',$data);
	}
	
    public function getEdit($id)
	{
		$data = array();
		$data['hunt'] = HuntModel::getInfo($id);
		$data['game'] = GameService::getGameInfo($data['hunt']['game_id']);
		return $this->display('hunt-info',$data);
	}
	
	public function postSave()
	{
		$id = Input::get('id');
		$name = Input::get('name');
		$game_id = Input::get('game_id');
		$startdate = strtotime(Input::get('startdate'));
		$enddate = strtotime(Input::get('enddate'));
		$status = Input::get('status',0);		
		//$reward = Input::only('reward_1_name','reward_1_num','reward_1_probability','reward_2_name','reward_2_num','reward_2_probability','reward_3_name','reward_3_num','reward_3_probability');
		$prize_id_1 = Input::get('prize_id_1');
		$prize_1 = PrizeModel::getInfo($prize_id_1);
		if($prize_1){
		    $prize_name_1 = $prize_1['name'];
		}else{
			return $this->back()->with('global_tips','一等奖没有选择奖品');
		}
		$num_1 = Input::get('num_1');
		$probability_1 = Input::get('probability_1');
		$first_prize = array('prize_id'=>$prize_id_1,'prize_name'=>$prize_name_1,'num'=>$num_1,'probability'=>$probability_1);
		
		$prize_id_2 = Input::get('prize_id_2');
	    $prize_2 = PrizeModel::getInfo($prize_id_2);
		if($prize_2){
		    $prize_name_2 = $prize_2['name'];
		}else{
			return $this->back()->with('global_tips','二等奖没有选择奖品');
		}
		$num_2 = Input::get('num_2');
		$probability_2 = Input::get('probability_2');
		$second_prize = array('prize_id'=>$prize_id_2,'prize_name'=>$prize_name_2,'num'=>$num_2,'probability'=>$probability_2);
		
		$prize_id_3 = Input::get('prize_id_3');
	    $prize_3 = PrizeModel::getInfo($prize_id_3);
		if($prize_3){
		    $prize_name_3 = $prize_3['name'];
		}else{
			return $this->back()->with('global_tips','三等奖没有选择奖品');
		}
		$num_3 = Input::get('num_3');
		$probability_3 = Input::get('probability_3');
		$third_prize = array('prize_id'=>$prize_id_3,'prize_name'=>$prize_name_3,'num'=>$num_3,'probability'=>$probability_3);
		
		$clicktimes = Input::get('clicktimes',3);
	    		
		$input = array(
		    'id'=>$id,
		    'name'=>$name,
		    'game_id'=>$game_id,
		    'startdate'=>$startdate,
		    'enddate'=>$enddate,
		    'reward'=>'',
		    'first_prize'=>json_encode($first_prize),
		    'second_prize'=>json_encode($second_prize),
		    'third_prize'=>json_encode($third_prize),
		    'status'=>$status,
		    'clicktimes'=>$clicktimes
		);
		$validator = Validator::make($input,array(
		    'name'=>'required',
		    'game_id'=>'required',
		    'startdate'=>'required',
		    'enddate'=>'required',
		));
		if($validator->fails()){
			if($validator->messages()->has('name')){
				return $this->back()->with('global_tips','宝箱名称不能为空');
			}
		    if($validator->messages()->has('game_id')){
				return $this->back()->with('global_tips','请选择入口游戏');
			}
		    if($validator->messages()->has('startdate')){
				return $this->back()->with('global_tips','请选择活动开始时间');
			}
		    if($validator->messages()->has('datedate')){
				return $this->back()->with('global_tips','请选择活动结束时间');
			}
		}
		$result = HuntModel::save($input);
		return $this->redirect('activity/hunt/list')->with('global_tips','寻宝箱添加成功');
	}
}