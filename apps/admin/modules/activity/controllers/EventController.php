<?php
namespace modules\activity\controllers;

use modules\activity\models\ActivityModel;
use modules\activity\models\GameAskModel;
use modules\activity\models\AskQuestionModel;
use modules\activity\models\PrizeModel;
use modules\forum\models\TopicModel;
use Yxd\Services\Cms\GameService;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\Core\BackendController;


class EventController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'activity';
	}
	
	public function getList($type=0)
	{
		$search = Input::only('type','startdate','enddate','keyword');
		if($type){
			$search['type'] = $type;
		}		
		$page = Input::get('page',1);
		$pagesize = 10;
		$data = array();				
		$result = ActivityModel::search($search,$page,$pagesize);
		$act_ids = array();
		foreach($result['result'] as $row){
			$act_ids[] = $row['id'];
		}		
		if($act_ids){
			$data['process'] = GameAskModel::getProcess($act_ids);			
		}
		
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$pager->appends($search);
		$data['datalist'] = $result['result'];
		$data['typelist'] = ActivityModel::$TypeList;
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $result['total'];	
		return $this->display('activity-list',$data);
	}
	
	public function getAdd($type=0)
	{
		$data = array();
		$data['typelist'] = ActivityModel::$TypeList;
		if($type){
			$data['activity'] = array('type'=>$type,'status'=>1);
		}
		return $this->display('activity-edit',$data);
	}
	
	public function getEdit($id)
	{
		$data = array();
		$activity = ActivityModel::getInfo($id);
		$data['activity'] = $activity;
		$game = GameService::getGameInfo($activity['game_id']);
		$data['game'] = $game;
		$data['typelist'] = ActivityModel::$TypeList;
		return $this->display('activity-edit',$data);
	}
	
	public function postSaveActivity()
	{
		$input['id'] = Input::get('id');
		$input['title'] = Input::get('title');
		$input['startdate'] = strtotime(Input::get('startdate'));
		$input['enddate'] = strtotime(Input::get('enddate'));
		$input['lotterytime'] = Input::get('lotterytime',0);
		$input['game_id'] = Input::get('game_id',0);
		$input['rule_id'] = Input::get('rule_id',0);
		//$input['status'] = Input::get('status',0);
		$input['sort'] = Input::get('sort',0);
		$input['type'] = (int)Input::get('type');
		$input['addtime'] = time();
	    $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    
	    //列表图
	    if(Input::hasFile('bigpic')){
	    	
			$file = Input::file('bigpic'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$input['bigpic'] = $dir . $new_filename . '.' . $mime;
		}
	    
	    //列表图
	    if(Input::hasFile('listpic')){
	    	
			$file = Input::file('listpic'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$input['listpic'] = $dir . $new_filename . '.' . $mime;
		}
		$rule = array(
		    'title'=>'required',		    
		    'startdate'=>'required',
		    'enddate'=>'required',
		);
		if($input['type'] == 1){
			$rule['game_id'] = 'required';
		}else{
			$rule['rule_id'] = 'required';
		}
	    $validator = Validator::make($input,$rule);
		if($validator->fails()){
			if($validator->messages()->has('title')){
				return $this->back()->with('global_tips','标题不能为空');
			}
		    if($validator->messages()->has('game_id')){
				return $this->back()->with('global_tips','请选择入口游戏');
			}
		    if($validator->messages()->has('rule_id')){
				return $this->back()->with('global_tips','活动帖不能为空');
			}
		    if($validator->messages()->has('startdate')){
				return $this->back()->with('global_tips','请选择活动开始时间');
			}
		    if($validator->messages()->has('datedate')){
				return $this->back()->with('global_tips','请选择活动结束时间');
			}
		}
		
		if($input['type'] == 2){
			$topic = TopicModel::getTopicInfo($input['rule_id']);
			if(!$topic){
				return $this->back()->with('global_tips','活动帖不存在');
			}
			if($topic['gid']){
				$input['game_id'] = $topic['gid'];
			}
		}
		
		$id = ActivityModel::save($input);
	    if($id){
			return $this->redirect('activity/event/list/' . $input['type'])->with('global_tips','活动保存成功');
		}
		
	}
	
	/**
	 * 开启
	 */
	public function getOpen($id)
	{
		$activity = ActivityModel::getInfo($id);		
		$process = GameAskModel::getProcess(array($id));
		if((int)$activity['type']==1){
			if(in_array($id,$process['prize_ids']) && in_array($id,$process['ask_ids']))
			{
				ActivityModel::updateStatus($id,1);
				return $this->back()->with('global_tips','活动已开启');
			}else{
				return $this->back()->with('global_tips','信息不全无法开启活动');
			}
		}else{
			if($activity['rule_id']){
				ActivityModel::updateStatus($id,1);
				return $this->back()->with('global_tips','活动已开启');
			}else{
				return $this->back()->with('global_tips','信息不全无法开启活动');
			}
		}
		
	}
	
	/**
	 * 关闭
	 */
    public function getClose($id)
	{
		ActivityModel::updateStatus($id,0);
		return $this->back()->with('global_tips','活动已关闭');
	}
}