<?php
namespace modules\admin\controllers;

use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Config as ConfigRule;
use Illuminate\Support\Facades\Paginator;
use modules\system\models\OperateModel;
use modules\system\models\AdminModel;
use ClassPreloader\Config;

class OperateController extends BackendController{
	public function _initialize(){
		$this->current_module = 'admin';
		$this->monolog_channel = ConfigRule::get('rule.monolog_channel');
	}
	
	public function getIndex(){
		$input = Input::only('page','name','group','channel','startdate','enddate');
		$page = $input['page']?$input['page']:1;
		$op_name = $input['name'];
		$group = $input['group'];
		$channel_search = array_search($input['channel'], $this->monolog_channel);
		$channel = $channel_search?$channel_search:$input['channel'];
		$time_arr = array(
			'start_date'=>$input['startdate'],
			'end_date'=>$input['enddate']
		);
		$pagesize = 10;
		$totalcount = OperateModel::getMonologCount($op_name,$group,$channel,$time_arr);
		$result = OperateModel::getMonolog($op_name,$group,$channel,$time_arr,$page,$pagesize);
		$group = $this->filtGroup(AdminModel::getGroupList());
		$data['list'] = array();
		if($result){
			foreach ($result as $k=>&$val){
				$val['name'] = $val['op_name'];
				$val['group'] = $val['op_group'];
				$val['channel'] = isset($this->monolog_channel[$val['channel']])?$this->monolog_channel[$val['channel']]:$val['channel'];
				$val['related_data'] = json_encode(unserialize($val['related_data']));
			}
		}
		$pager = Paginator::make(array(),$totalcount,$pagesize);
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $totalcount;
		$data['list'] = $result;
		$data['group'] = $group;
		$data['search'] = array(
			'name'=>$op_name,
			'group'=>$input['group'],
			'channel'=>$input['channel'],
			'startdate'=>$time_arr['start_date'],
			'enddate'=>$time_arr['end_date']
		);
		return $this->display('operate-list',$data);
	}
	
	private function filtGroup($group){
		if(!$group) return false;
		$new_group = array('0'=>'不限');
		foreach ($group as $g){
			$new_group[$g['name']] = $g['description'];
		}
		return $new_group;
	}
	
	public function getGetLoginfo(){
		if (Request::ajax())
		{
			$mon_id = Input::get('mon_id');
			$log_info = OperateModel::getMonologById($mon_id);
			if($log_info) $log_info = unserialize($log_info['related_data']);
			return unserialize($log_info['data']);
		}
	}
}