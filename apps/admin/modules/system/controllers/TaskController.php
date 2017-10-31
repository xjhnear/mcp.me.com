<?php
namespace modules\system\controllers;

use Yxd\Modules\Core\CacheService;

use modules\system\models\SystemSettingModel;
use Yxd\Modules\System\SettingService;

use modules\forum\models\TopicModel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Yxd\Modules\Core\BackendController;
use modules\system\models\CreditModel;
use modules\system\models\TaskModel;

class TaskController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'system';
	}
	
    public function getIndex()
	{
		$data = array();
		$data['tasklist'] = TaskModel::getTaskList();
		$data['tasknum'] = TaskModel::getTaskFinishNum();
		return $this->display('task-list',$data);
	}
	
	public function getCreate()
	{
		$data = array();
		$data['tasktype'] = Config::get('yxd.task.tasktype');
		return $this->display('task-edit',$data);
	}
	
	public function getEdit($id)
	{
		$data = array();
		$data['task'] = TaskModel::getTaskInfo($id);
		$data['tasktype'] = Config::get('yxd.task.tasktype');
		return $this->display('task-edit',$data);
	}
	
	public function postSave()
	{
		$id = Input::get('id');
		$input['step_name'] = Input::get('step_name');
		$input['step_desc'] = Input::get('step_desc');
		$input['type'] = Input::get('type');
		$tasktype = Config::get('yxd.task.tasktype');
		$input['typename'] = $tasktype[$input['type']];
		$score = Input::get('score');
		$closed = Input::get('closed',0);
		$experience = Input::get('experience');
		$input['reward'] = json_encode(array('score'=>$score,'experience'=>$experience));
		$action = Input::get('action');
		$condition_num = (int)Input::get('condition_num',1);
		$max_times = (int)Input::get('max_times',1);
		$input['condition'] = json_encode(array($action=>$condition_num,'max_times'=>$max_times,'closed'=>$closed));
		TaskModel::save($input,$id);
		TaskModel::saveSystemSetting($action, $score);
		CacheService::forget('table::task');
		return $this->redirect('system/task/index')->with('global_tips','任务修改成功');
	}
	//获取签到规定的数据
	public function getCheckin()
	{
		$data = array();
	    $config = SettingService::getConfig('checkin_setting');
		if($config){
			$data['checkin'] = $config['data'];
		}
		return $this->display('task-checkin',$data);
	}
	
	//获取推广规则的数据
	public function getTuiguang()
	{
		$data = array();
		$config = SettingService::getConfig('tuiguang_setting');
		if($config){
			$data['tuiguang'] = $config['data'];
		}
		return $this->display('task-tuiguang', $data);
	}
	//签到设置的保存
	public function postSaveCheckin()
	{
		$input = Input::only('first_day','second_day','third_day','fourth_day','fifth_day','sixth_day','seventh_day','greater_seven_day');
		
		SettingService::setConfig('checkin_setting', $input);
		return $this->redirect('system/task/checkin');
	}
	
	//推广任务的保存
	public function postSaveTuiguang()
	{
		$input = Input::only('newtuiguang_1','oldtuiguang_1','oldtuiguang_10','oldtuiguang_100','oldtuiguang_500','oldtuiguang_1000');
		SettingService::setConfig('tuiguang_setting', $input);
		if($input){
			TaskModel::syncTaskScore($input);
		}
		return $this->redirect('system/task/tuiguang');
	}
	
	public function getEditCheckin()
	{
		$uid = 100241;
		$data = array();
		$datalist = array();
		for($i=18;$i<=30;$i++){
			
			$datalist[] = array('time'=>strtotime('2014-06-'.$i),'date'=>'2014-06-'.$i);
		}
		$datalist[] = array('time'=>strtotime('2014-07-01'),'date'=>'2014-07-01');
		$datalist[] = array('time'=>strtotime('2014-07-02'),'date'=>'2014-07-02');
		
		$checkinlist = TaskModel::getCheckinList($uid);
		$data['checkinlist'] = $checkinlist;
		$data['datalist'] = $datalist;
		return $this->display('test-checkin',$data);
	}
	
	public function postSaveEditCheckin()
	{
		$uid = 100241;
		$times = Input::get('time');
		if($times){
			$data = array();
			foreach($times as $time){
				$data[] = array('uid'=>$uid,'ctime'=>$time);
			}
			if($data){
				TaskModel::saveCheckin($uid, $data);
			}
		}
		return $this->back();
	}
}