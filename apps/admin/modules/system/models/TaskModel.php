<?php
namespace modules\system\models;
use Yxd\Modules\Core\BaseModel;
use Illuminate\Support\Facades\DB;
use Yxd\Modules\Core\CacheService;

class TaskModel extends BaseModel
{
    public static function getTaskList()
	{
		$tasks = self::dbClubMaster()->table('task')->orderBy('type','asc')->get();
		foreach($tasks as $key=>$row){
			$row['reward'] = json_decode($row['reward'],true)==null ? array() : json_decode($row['reward'],true);
			$row['condition'] = json_decode($row['condition'],true)==null ? array() : json_decode($row['condition'],true);
			$tasks[$key] = $row;
		}
		return $tasks;
	}
	
	public static function getTaskFinishNum()
	{
		return self::dbClubMaster()->table('task_account')->select(DB::raw('task_id,count(*) as total'))->where('status','=',1)->groupBy('task_id')->lists('total','task_id');
	}
	
	public static function getTaskInfo($id)
	{
		$task = self::dbClubMaster()->table('task')->where('id','=',$id)->first();
		$condition = json_decode($task['condition'],true)==null ? array() : json_decode($task['condition'],true);
		$reward = json_decode($task['reward'],true)==null ? array() : json_decode($task['reward'],true);
		$task['condition'] = $condition;
		return array_merge($task,$reward);
	}
	
	public static function save($data,$id=0)
	{
		if($id){
			return self::dbClubMaster()->table('task')->where('id','=',$id)->update($data);
		}
	}
	//修改system_setting表中的数据
	public static function saveSystemSetting($action, $score)
	{
		$data = self::dbClubMaster()->table('system_setting')->where('keyname', 'tuiguang_setting')->pluck('data');
		$data = unserialize($data);
		foreach($data as $key=>$v){
			if($key == $action){
				$data[$key]=$score;
			}
		}
		$data = serialize($data);
		$data2 = array('data'=>$data);
		self::dbClubMaster()->table('system_setting')->where('keyname', 'tuiguang_setting')->update($data2);
		CacheService::forget('tuiguang_setting');
	}
	
	public static function saveCheckin($uid,$data)
	{
		self::dbClubMaster()->table('checkinfo')->where('uid','=',$uid)->delete();
		self::dbClubMaster()->table('checkinfo')->insert($data);
	}
	
	public static function getCheckinList($uid)
	{
		return self::dbClubMaster()->table('checkinfo')->where('uid','=',$uid)->lists('ctime');
	}
	
	/**
	 * 同步推广任务游币设置
	 */
	public static function syncTaskScore($data)
	{
	    foreach($data as $key=>$v){
			if($key=='oldtuiguang_1'){
				self::dbClubMaster()->table('task')->where('action','=',$key)->update(array('reward'=>json_encode(array('score'=>$v, 'experience'=>0))));
				continue;
			}elseif($key=='oldtuiguang_10'){
				self::dbClubMaster()->table('task')->where('action','=',$key)->update(array('reward'=>json_encode(array('score'=>$v, 'experience'=>0))));
				continue;
			}elseif($key=='oldtuiguang_100'){
				self::dbClubMaster()->table('task')->where('action','=',$key)->update(array('reward'=>json_encode(array('score'=>$v, 'experience'=>0))));
				continue;
			}elseif($key=='oldtuiguang_500'){
				self::dbClubMaster()->table('task')->where('action','=',$key)->update(array('reward'=>json_encode(array('score'=>$v, 'experience'=>0))));
				continue;
			}elseif($key=='oldtuiguang_1000'){
				self::dbClubMaster()->table('task')->where('action','=',$key)->update(array('reward'=>json_encode(array('score'=>$v, 'experience'=>0))));
				continue;
			}
		}
	}
}