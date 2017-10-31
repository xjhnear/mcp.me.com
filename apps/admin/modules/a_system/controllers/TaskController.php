<?php
namespace modules\a_system\controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Yxd\Modules\Core\BackendController;

use modules\a_system\models\TaskModel;

class TaskController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'a_system';
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
		
		return $this->redirect('a_system/task/index')->with('global_tips','任务修改成功');
	}	
}