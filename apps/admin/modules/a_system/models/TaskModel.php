<?php
namespace modules\a_system\models;
use Yxd\Modules\Core\BaseModel;
use Illuminate\Support\Facades\DB;
use Yxd\Modules\Core\CacheService;
use Youxiduo\Android\Model\Task;
use Youxiduo\Android\Model\TaskAccount;

class TaskModel extends BaseModel
{
    public static function getTaskList()
	{
		$tasks = Task::db()->orderBy('type','asc')->get();
		foreach($tasks as $key=>$row){
			$row['reward'] = json_decode($row['reward'],true)==null ? array() : json_decode($row['reward'],true);
			$row['condition'] = json_decode($row['condition'],true)==null ? array() : json_decode($row['condition'],true);
			$tasks[$key] = $row;
		}
		return $tasks;
	}
	
	public static function getTaskFinishNum()
	{
		return TaskAccount::db()->select(DB::raw('task_id,count(*) as total'))->where('status','=',1)->groupBy('task_id')->lists('total','task_id');
	}
	
	public static function getTaskInfo($id)
	{
		$task = Task::db()->where('id','=',$id)->first();
		$condition = json_decode($task['condition'],true)==null ? array() : json_decode($task['condition'],true);
		$reward = json_decode($task['reward'],true)==null ? array() : json_decode($task['reward'],true);
		$task['condition'] = $condition;
		return array_merge($task,$reward);
	}
	
	public static function save($data,$id=0)
	{
		if($id){
			return Task::db()->where('id','=',$id)->update($data);
		}
	}	
}