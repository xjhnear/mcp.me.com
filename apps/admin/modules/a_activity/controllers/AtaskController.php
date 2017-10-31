<?php
namespace modules\a_activity\controllers;

use Yxd\Services\Models\ActivityAsk;

use Youxiduo\Android\Control\TaskApi;

use Youxiduo\Android\Model\Checkinfo;
use Yxd\Modules\System\SettingService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Youxiduo\Helper\Utility;
use Youxiduo\Android\Model\Game;
use Youxiduo\Message\Model\MessageType;

use Youxiduo\Android\Model\Activity;
use Youxiduo\Android\Model\ActivityTask;
use Youxiduo\Android\Model\ActivityTaskUser;
use Youxiduo\Android\Model\ActivityTaskUserScreenshot;
use Youxiduo\Android\Model\CheckinsTask;
use Youxiduo\Android\Model\CheckinsTaskUser;
use Yxd\Services\UserService;
use Youxiduo\V4\User\MoneyService;

use Youxiduo\Android\TaskService;

class AtaskController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'a_activity';
	}
	
	/**
	 * 任务列表
	 */
	public function getTlist()
	{
		$action_type = Input::get('action_type');//1|2|3
		$pageIndex = Input::get('page',1);
		$title = Input::get('title','');
		$complete_type = Input::get('complete_type');
		$pageSize = 10;	
		$data = array();
		$data['action_type'] = $action_type;
		$data['title'] = $title;
		$data['complete_type'] = $complete_type;
		if($action_type==1){
			$data['menu_name'] = '试玩任务';
		}elseif($action_type==2){
			$data['menu_name'] = '分享任务';
		}elseif($action_type==3){
			$data['menu_name'] = '代充任务';
		}
		$search = array('action_type'=>$action_type);
		if($title){
			$search['title'] = $title;
		}
		if($complete_type){
			$search['complete_type'] = $complete_type;
		}
		$total = ActivityTask::searchCount($search);
		$result = ActivityTask::searchList($search,$pageIndex,$pageSize,array('id'=>'desc'));
		$pager = Paginator::make(array(),$total,$pageSize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['datalist'] = $result;
		$data['conditions'] = array('download'=>'下载','running'=>'试玩','screenshot'=>'截图');
	    $game_ids = array();
		foreach($result as $row){
			$game_ids[] = $row['gid'];
		}
		if($game_ids){
			$games = Game::getListByIds($game_ids);
			$data['games'] = $games;
		}
		return $this->display('task-run-list',$data);
	}
	
	public function getDoStatistic()
	{
		ActivityTaskUser::updateStatusNum();
		return $this->back('统计完成');
	}
	
	public function getDoTask()
	{
		$id = Input::get('id');
		$action_type = Input::get('action_type');		
		$data = array();				
		$data['action_types'] = array('1'=>'试玩任务','2'=>'分享任务','3'=>'代充任务');
		$data['conditions'] = array('download'=>'下载','running'=>'试玩','screenshot'=>'截图');
		$data['formset'] = Config::get('yxd.charge_form');
		if($id){
			$info = ActivityTask::findOne(array('id'=>$id));
			$data['atask'] = $info;
		}else{
			$data['atask'] = array('action_type'=>$action_type,'is_show'=>1,'reward_type'=>'money');
		}
		return $this->display('task-run-edit',$data);
	}
	
    public function postDoTask()
	{
		$input = array();
		$input['id'] = Input::get('id');
		$input['action_type'] = Input::get('action_type');
		$input['title'] = Input::get('title');
		$input['gid'] = Input::get('gid');
		$input['complete_type'] = Input::get('complete_type');
		$input['game_package_name'] = Input::get('game_package_name');
		$input['start_time'] = Input::get('start_time') ? strtotime(Input::get('start_time') . ' 00:00:00') : null;
		$input['end_time'] = Input::get('end_time') ? strtotime(Input::get('end_time').' 23:59:59') : null;
		$input['reward_type'] = Input::get('reward_type');							
		$input['is_show'] = (int)Input::get('is_show',0);
		$input['is_top'] = (int)Input::get('is_top',0);
		$input['sort'] = (int)Input::get('sort',0);
		$input['content'] = Input::get('content');		
		$input['total_time'] = Input::get('total_time',0);
		$input['total_num'] = (int)Input::get('total_num',0);
		if(!$input['id']){
			$input['last_num'] = $input['total_num'];
		}
		$input['is_relation_task'] = (int)Input::get('is_relation_task',0);
		$input['relation_task_id'] = (int)Input::get('relation_task_id',0);
		
		$rules = array(
		    'action_type'=>'required',
		    'title'=>'required',
		    'gid'=>'required',
		    'game_package_name'=>'required',
		    'start_time'=>'required',
		    'end_time'=>'required',
		    'content'=>'required'
		);
		
	    if($input['reward_type']=='money'){
			$input['money'] = Input::get('money');
			$rules['money'] = 'required';
		}elseif($input['reward_type']=='giftbag'){
			$input['giftbag_id'] = Input::get('giftbag_id');
			$rules['giftbag_id'] = 'required';
		}else{
			$input['goods_id'] = Input::get('goods','');
			$rules['goods_id'] = 'required';
		}
		
		if($input['action_type']==1){
			if($input['complete_type']=='running'){
				$rules['complete_type'] = 'required';
				$rules['total_time'] = 'required';
			}
		}elseif($input['action_type']==2){
			$dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
		    $path = storage_path() . $dir;
		    $input['share_title'] = Input::get('share_title');
		    $input['share_weixin'] = Input::get('share_weixin');
		    $input['share_redirect_url'] = Input::get('redirect_url');
		    $input['ip_limit_times'] = Input::get('ip_limit_times');
		    if(Input::hasFile('icon')){
		    	
				$file = Input::file('icon'); 
				$new_filename = date('YmdHis') . str_random(4);
				$mime = $file->getClientOriginalExtension();			
				$file->move($path,$new_filename . '.' . $mime );
				$input['share_icon'] = $dir . $new_filename . '.' . $mime;
			}else{
				$input['share_icon'] = Input::get('share_icon');
			}
			
			$rules['share_title'] = 'required';
			$rules['share_icon'] = 'required';
			$rules['share_weixin'] = 'required';
			$rules['share_redirect_url'] = 'required';
			$rules['ip_limit_times'] = 'required';
			
		}elseif($input['action_type']==3){
			
		}
	    $validator = Validator::make($input,$rules);
		if($validator->fails()){
			if($validator->messages()->has('action_type')){
				return $this->back()->with('global_tips','任务类型错误');
			}
		    if($validator->messages()->has('title')){
				return $this->back()->with('global_tips','标题不能为空');
			}
			if($validator->messages()->has('gid')){
				return $this->back()->with('global_tips','请选择游戏');
			}			
			if($validator->messages()->has('game_package_name')){
				return $this->back()->with('global_tips','游戏包名不能为空');
			}
			if($validator->messages()->has('start_time')){
				return $this->back()->with('global_tips','任务开始时间不能为空');
			}
			if($validator->messages()->has('end_time')){
				return $this->back()->with('global_tips','任务结束时间不能为空');
			}
			if($validator->messages()->has('money')){
				return $this->back()->with('global_tips','奖励游币不能为空');
			}
		    if($validator->messages()->has('giftbag_id')){
				return $this->back()->with('global_tips','奖励礼包不能为空');
			}
		    if($validator->messages()->has('goods_id')){
				return $this->back()->with('global_tips','奖励商品不能为空');
			}
			if($validator->messages()->has('content')){
				return $this->back()->with('global_tips','任务内容不能为空');
			}
		    if($validator->messages()->has('complete_type')){
				return $this->back()->with('global_tips','请选择试玩完成条件');
			}
		    if($validator->messages()->has('total_time')){
				return $this->back()->with('global_tips','试玩时长不能为空');
			}	
		    if($validator->messages()->has('share_title')){
				return $this->back()->with('global_tips','分享标题不能为空');
			}
		    if($validator->messages()->has('share_icon')){
				return $this->back()->with('global_tips','分享ICON不能为空');
			}
		    if($validator->messages()->has('share_weixin')){
				return $this->back()->with('global_tips','分享内容不能为空');
			}
		    if($validator->messages()->has('share_redirect_url')){
				return $this->back()->with('global_tips','分享的URL不能为空');
			}	    
		}
		
		if($input['action_type']==1){
			$input['complete_condition'] = json_encode(array('total_time'=>$input['total_time']));
		}elseif($input['action_type']==2){
			$input['complete_condition'] = json_encode(array('share_title'=>$input['share_title'],'share_icon'=>$input['share_icon'],'share_weixin'=>$input['share_weixin'],'share_redirect_url'=>$input['share_redirect_url'],'ip_limit_times'=>$input['ip_limit_times']));
			$input['complete_type'] = 'share';
		}elseif($input['action_type']==3){
			$fields = Config::get('yxd.charge_form');
			$form_fields = array();
			foreach($fields as $key=>$val){
				if(Input::has($key)){
					$form_fields[$key]=$val;
				}
			}
			$input['complete_condition'] = json_encode($form_fields);
			$input['complete_type'] = 'recharge';
		}
		
		unset($input['total_time']);
		unset($input['share_title']);
		unset($input['share_icon']);
		unset($input['share_weixin']);
		unset($input['share_redirect_url']);
		unset($input['ip_limit_times']);
		$success = ActivityTask::save($input);
		if($success){
			return $this->redirect('a_activity/atask/tlist?action_type='.$input['action_type'],'数据保存成功');
		}else{
			return $this->back('数据保存失败');
		}
	}
	
	public function getDoDel($id)
	{
		$res = ActivityTask::delete($id);
		if($res) return $this->back('数据删除成功');
		return $this->back('数据删除失败');
	}
	
	public function getDoReward()
	{
		$id = (int)Input::get('id');
		$atid = (int)Input::get('atid');
		$search['id'] = $atid;
		$search['is_show'] = 1;		
		$task = ActivityTask::findOne($search);
		$user_task = ActivityTaskUser::findOne(array('id'=>$id));
		if($user_task['reward_status']==0){
		    $money = $task['money'];
		    $uid = $user_task['uid'];
			$info = '任务奖励:'.$task['title'].'';
			$reward_success = MoneyService::doAccount($uid,$money,'reward',$info);					
			if($reward_success) {
				ActivityTaskUser::updateStatus($id,array('reward_status'=>1,'reward_time'=>time()));
				$title = $task['title'].'任务完成';
		    	$message = '完成'.$task['title'].'任务奖励'.$money.'游币';
		    	TaskApi::sendTaskMessage($uid,$title,$message,23);
		    	return $this->back('奖励发放完成');					
			}
			return $this->back('奖励发放失败');
		}
		return $this->back('奖励发放失败');
	}
	
	public function getQueryTaskUser()
	{
		$atid = Input::get('atid');
		$uid = Input::get('uid');
		$complete_status = (int)Input::get('complete_status');
		$reward_status = (int)Input::get('reward_status',-1);
		$start_time = Input::get('start_time');
		$end_time = Input::get('end_time');
		$pageIndex = Input::get('page',1);
		$pageSize = 10;
        $task = ActivityTask::findOne(array('id'=>$atid));
		$search = array('atid'=>$atid);
		if($uid){
			$search['uid'] = $uid;
		}
		if($complete_status){
			$search['complete_status'] = $complete_status;
		}
		if($reward_status>-1){
			$search['reward_status'] = $reward_status;
		}
		if($start_time){
			$search['start_time'] = $start_time;
		}
		if($end_time){
			$search['end_time'] = $end_time;
		}
		$total = ActivityTaskUser::searchCount($search);
		$result = ActivityTaskUser::searchList($search,$pageIndex,$pageSize,array('id'=>'desc'));
        //添加黑名单数据
        foreach($result as $k=>$v){
            $black_res = TaskService::find_blacklist_by_key('uid',$v['uid']);
            if(date('Y-m-d')>=$black_res['createtime']&&date('Y-m-d')<=$black_res['endtime']){
                $result[$k]['black']=1;
            }else{
                $result[$k]['black']=0;
            }
        }
		$pager = Paginator::make(array(),$total,$pageSize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['datalist'] = $result;
		$data['totalCount'] = $total;
		$data['atid'] = $atid;
		$data['uid'] = $uid;
		$data['complete_status'] = $complete_status;
		$data['reward_status'] = $reward_status;
		$uids = array();
		$task_ids = array();
		foreach($result as $row){
			$uids[] = $row['uid'];
			$task_ids[] = $row['atid'];
		}
		$users = UserService::getBatchUserInfo($uids);
		$_tasks = ActivityTask::searchList(array('in_ids'=>$task_ids),1,$pageSize);
		$tasks = array();
		foreach($_tasks as $row){
			$tasks[$row['id']] = $row;
		}
		$data['users'] = $users;
		$data['task'] = $task;
		$data['tasks'] = $tasks;
		$data['complete_status_list'] = array('0'=>'全部','1'=>'已完成','2'=>'待审核','3'=>'未通过');
		$data['reward_status_list'] = array('-1'=>'全部','0'=>'未奖励','1'=>'已奖励');
		return $this->display('task-user-query',$data);
	}
	
	public function getLookScreenshot()
	{
		$uid = Input::get('uid');
		$atid = Input::get('atid');
		$data = array();
		$data['datalist'] = ActivityTaskUserScreenshot::searchList(array('atid'=>$atid,'uid'=>$uid));
		$html = $this->html('task-screenshot-list',$data);
		return $this->json(array('html'=>$html));
	}
	
	public function getDoCompleteScreenshot()
	{
		$id = Input::get('id');
		$atid = Input::get('atid');
		$complete_status = (int)Input::get('complete_status');
	    $task = ActivityTask::findOne(array('id'=>$atid));
		$task_user = ActivityTaskUser::findOne(array('id'=>$id));
		if($task && $task_user){
		if($task_user['complete_status']==1 && $task_user['reward_status']==1) return $this->back('已审核过不能重复审核');
			if($task_user['complete_status']!=1){			
			    $complete = ActivityTaskUser::updateCompleteStatus($id,$complete_status);
			}else{	
				$complete = true;
			}		
			if($complete && $complete_status==1){
				$reward_status = $task_user['reward_status'];
				if($reward_status !=1){
					$money = $task['money'];
					if($money<=0) return $this->back('任务奖励不能为零,奖励发放失败');
					$uid = $task_user['uid'];
					$info = '任务奖励:'.$task['title'].'';
					$success = MoneyService::doAccount($uid,$money,'screenshot_task',$info);
					if($success){
						$reward_success = ActivityTaskUser::updateRewardStatus($id,1);
						$message = '完成' . $task['title'] . '任务,奖励'.$money.'游币';
						TaskApi::sendTaskMessage($uid,$info,$message,23);
						if($reward_success) return $this->back('操作完成,奖励已发放'); 
					}else{
						return $this->back('操作完成,奖励发放失败'); 
					}
				}
			}elseif($complete_status==3){
				$reward_success = ActivityTaskUser::updateRewardStatus($id,-1);
				$uid = $task_user['uid'];
				$info = $task['title'] . '任务失败';
				$message = '尊敬的用户，您参加的'.$task['title'].'试玩截图活动，由于上传的图片不符合活动规则，导致此次任务失败。如有疑问请点击右下方“用户反馈”联系客服。';
				TaskApi::sendTaskMessage($uid,$info,$message,24,$atid);
				return $this->back('操作完成'); 
			}
		}
		return $this->back('操作失败');
	}
	
	public function getDoCompleteRecharge()
	{
		$id = Input::get('id');
		$atid = Input::get('atid');
		$complete_status = (int)Input::get('complete_status');
		$task = ActivityTask::findOne(array('id'=>$atid));
		$task_user = ActivityTaskUser::findOne(array('id'=>$id));
		if($task && $task_user){
			if($task_user['complete_status']==1 && $task_user['reward_status']==1) return $this->back('已审核过不能重复审核');
			if($task_user['complete_status']!=1){			
			    $complete = ActivityTaskUser::updateCompleteStatus($id,$complete_status);
			}else{	
				$complete = true;
			}		
			if($complete && $complete_status==1){
				$reward_status = $task_user['reward_status'];
				if($reward_status !=1){
					$money = $task['money'];
					if($money<=0) return $this->back('任务奖励不能为零,奖励发放失败');
					$uid = $task_user['uid'];
					$info = '任务奖励:'.$task['title'].'';
					$success = MoneyService::doAccount($uid,$money,'recharge_task',$info);
					if($success){
						$reward_success = ActivityTaskUser::updateRewardStatus($id,1);
						$message = '完成' . $task['title'] . '任务,奖励'.$money.'游币';
						TaskApi::sendTaskMessage($uid,$info,$message,23);
						if($reward_success) return $this->back('操作完成,奖励已发放'); 
					}else{
						return $this->back('操作完成,奖励发放失败'); 
					}
				}
			}elseif($complete_status==3){
				$reward_success = ActivityTaskUser::updateRewardStatus($id,-1);
				$uid = $task_user['uid'];
				$info = $task['title'] . '任务失败';
				$message = '尊敬的用户，您参加的'.$task['title'].'充值返现活动，由于没有您的充值记录，导致此次任务失败。如有疑问请点击右下方“用户反馈”联系客服。';
				TaskApi::sendTaskMessage($uid,$info,$message,24,$atid);
				return $this->back('操作完成'); 
			}
		}
		return $this->back('操作完成');
	}
	
	public function getRechargeInfo()
	{
		$atid = Input::get('atid');
		$uid = Input::get('uid');
		$gid = 'recharge_'.$atid;
		$info = Utility::loadByHttp(Config::get('app.android_api_url').'android_activity/charge_info_list',array('uid'=>$uid,'gid'=>$gid));
		
		if(!$info['errorCode'] && $info['result']) $info = $info['result'][0];
		$data['info'] = $info;
		$html = $this->html('pop-recharge-info',$data);
		return $this->json(array('html'=>$html));
	}
	
	/**
	 * 签到列表
	 */
	public function getCheckins()
	{
		$type = Input::get('type');//novice|running|cumulative
		$data = array();
		$search = array();
		$pageIndex = Input::get('page',1);
		$pageSize = 10;
		if($type == 'novice'){
			$start = Input::get('startdate',date('Y-m-d'));
			$end   = Input::get('enddate',date('Y-m-d'));
			$uid   = Input::get('uid');
			$search = array('startdate'=>$start,'enddate'=>$end,'uid'=>$uid);
			$result = $this->checkinsReport($search,$pageIndex,$pageSize);
			$days = (strtotime($end)-strtotime($start))/(3600*24);
			$uids = array();
			foreach($result['result'] as $row){
				$uids[] = $row['uid'];
			}
			$users = UserService::getBatchUserInfo($uids);
			$pager = Paginator::make(array(),$result['totalCount'],$pageSize);			
			$search['type'] = 'novice';
			$pager->appends($search);
			$data['pagelinks'] = $pager->links();			
			$data['search'] = $search;
			$data['datalist'] = $result['result'];
			$data['days'] = $days+1;
			$data['users'] = $users;
			
			return $this->display('task-checkins-novice',$data);
		}elseif($type=='running'){
			$search['type'] = 'running';
			$data['datalist'] = CheckinsTask::searchList($search,$pageIndex,$pageSize);
			$totalCount = CheckinsTask::searchCount($search);
			$pager = Paginator::make(array(),$totalCount,$pageSize);
			$pager->appends($search);
			$data['pagelinks'] = $pager->links();
			return $this->display('task-checkins-running',$data);
		}elseif($type=='cumulative'){
			$search['type'] = 'cumulative';
			$data['datalist'] = CheckinsTask::searchList($search,$pageIndex,$pageSize);
			$totalCount = CheckinsTask::searchCount($search);
			$pager = Paginator::make(array(),$totalCount,$pageSize);
			$pager->appends($search);
			$data['pagelinks'] = $pager->links();
			return $this->display('task-checkins-cumulative',$data);
		}
		
	}
	
	protected function checkinsReport($search,$pageIndex,$pageSize)
	{	
		$params = array(strtotime($search['startdate']),(strtotime($search['enddate'])+3600*24-1));			
		$sql_get = 'select uid,count(*) as times from yxd_checkinfo where ctime >= ? and ctime <= ? ';
		if(isset($search['uid']) && $search['uid']){
			$sql_get .= ' and uid = ?';
			$params[] = $search['uid'];
		}
		$sql_get .= ' group by uid';
		$sql_count = 'select count(*) as total from ('.$sql_get.') as a';
		$sql_get .= ' order by times desc limit ?,?';
		$index = ($pageIndex-1)* $pageSize;
		$offset = $pageSize;
		
		$total = Checkinfo::execQuery($sql_count,$params);
		$params[] = $index;
		$params[] = $offset;
		$result = Checkinfo::execQuery($sql_get,$params);
		return array('result'=>$result,'totalCount'=>$total[0]['total']);
	}
	
	public function getDoCheckins()
	{
		$data = array();
		$data['reward_types'] = array('money'=>'游币','giftbag'=>'礼包','goods'=>'实物');
		$id = Input::get('id');		
		if($id){
			$search['id'] = $id;
			$info = CheckinsTask::findOne($search);
			$type = $info['type'];
			$data['info'] = $info;
		}else{
		    $type = Input::get('type');
		}
	    if($type=='running'){
			return $this->display('task-checkins-running-edit',$data);
		}elseif($type=='cumulative'){
			return $this->display('task-checkins-cumulative-edit',$data);
		}
	}
	
	public function postDoCheckins()
	{
		$id = Input::get('id');
		$type = Input::get('type');
		$title = Input::get('title');
		$start_time = Input::get('start_time');
		$end_time = Input::get('end_time');
		$is_show = (int)Input::get('is_show',0);		
		
	    $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    if(Input::hasFile('filedata')){	    	
			$file = Input::file('filedata'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$reward_img = $dir . $new_filename . '.' . $mime;
		}else{
			$reward_img = Input::get('reward_img');
		}
		$reward_days = (int)Input::get('reward_days');
		$reward_type = Input::get('reward_type');
		$reward_value = Input::get('reward_value');
	    
		$input = array();
		$input['id'] = $id;
		$input['type'] = $type;
		$input['title'] = $title;
		$input['start_time'] = strtotime($start_time);
		$input['end_time'] = strtotime($end_time)+3600*24-1;
		$input['is_show'] = $is_show;
		$input['reward_img'] = $reward_img;
		$input['reward_days'] = $reward_days;
		$input['reward_type'] = $reward_type;
		$input['reward_value'] = $reward_value;
		
		$success = CheckinsTask::save($input);
		return $this->redirect('a_activity/atask/checkins?type='.$type,'保存成功');
	}
	
	public function getQueryCheckins()
	{
		$ctid = Input::get('ctid');
		$uid = Input::get('uid');	
		$pageIndex = Input::get('page',1);
		$pageSize = 100;	
		$data = array();
		$data['ctid'] = $ctid;
		$data['uid'] = $uid;
		$search = array('ctid'=>$ctid,'uid'=>$uid);
		$total = CheckinsTaskUser::searchCount($search);
		$result = CheckinsTaskUser::searchList($search,$pageIndex,$pageSize);
		
		$uids = array();
		foreach($result as $row){
			$uids[] = $row['uid'];
		}
		$users = UserService::getBatchUserInfo($uids);
		$data['users'] = $users;
		$pager = Paginator::make(array(),$total,$pageSize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['datalist'] = $result;
		return $this->display('task-checkins-query',$data);
	}
	
	public function getAgreementEdit()
	{
		$data = array();
	    $config = SettingService::getConfig('android_checkins_agreement');
		if($config){
			$data['content'] = $config['data']['content'];
		}
		return $this->display('task-checkins-agreement',$data);
	}
	
    public function postAgreementEdit()
	{
		$input = array();
		$input['content'] = Input::get('content','');
		SettingService::setConfig('android_checkins_agreement', $input);
		return $this->back('保存成功');
	}

    public function  getBlacklist()
    {
        $uid= Input::get('uid');
        $pageIndex = Input::get('page',1);
        $pageSize = 10;

        $search = array();
        if($uid){
            $search['uid'] = $uid;
        }
        $total = TaskService::find_blacklist_count($search);
        $result = TaskService::find_blacklist($search,$pageIndex,$pageSize);

        $data['list'] = $result;
        $data['uid'] = $uid;
        $pager = Paginator::make(array(),$total,$pageSize);
        $pager->appends($search);
        $data['pagelinks'] = $pager->links();
        return $this->display('task-blacklist',$data);
    }
    public function postTaskBlacklistSave()
    {
        $data = Input::get();
        if(!isset($data['uid'])||!isset($data['date_num'])){
            echo json_encode(array('success'=>false,'mess'=>'数据不完整，请核实','data'=>null));
        }else{
            $day_m = $data['date_num']-1;
            $day2 = date("Y-m-d",strtotime("+{$day_m} day"));
            $data['endtime'] = $day2;
            $res = TaskService::doSaveBlacklist($data);
            if($res){
                echo json_encode(array('success'=>true,'mess'=>'保存成功','data'=>$res,'desc'=>date("Y-m-d")."至".$day2));
            }else{
                echo json_encode(array('success'=>false,'mess'=>'数据未发生改变或保存失败','data'=>$res));
            }

        }

    }

    public function getTaskBlacklistDel()
    {
        $id = Input::get('id');
        if(isset($id)){
            $res = TaskService::del_blacklist($id);
            if($res){
                echo json_encode(array('success'=>true,'mess'=>'删除成功','data'=>$res));
            }else{
                echo json_encode(array('success'=>false,'mess'=>'删除失败或数据已不存在','data'=>$res));
            }
        }else{
            echo json_encode(array('success'=>false,'mess'=>'未传入参数','data'=>null));
        }

    }
}