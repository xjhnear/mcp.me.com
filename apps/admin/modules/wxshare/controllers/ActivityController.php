<?php
namespace modules\wxshare\controllers;

use Yxd\Services\UserService;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\Core\BackendController;

use Youxiduo\Activity\Share\ActivityService;
use Youxiduo\Activity\Share\GiftbagService;
use Youxiduo\Activity\Share\GoodsService;
use Youxiduo\Activity\Share\RechargeService;

class ActivityController extends BackendController
{
    public function _initialize()
	{
		$this->current_module = 'wxshare';
	}
	
	public function getList()
	{
		$search = array();
		$data = array();
		$pageIndex = Input::get('page',1);
		$pageSize = 10;
		$result = ActivityService::search($search,$pageIndex,$pageSize);
		$data['datalist'] = $result['result'];
		return $this->display('activity-list',$data);
	}
	
	public function getEdit($id=0)
	{
		$data = array();
		if($id){
			$data['activity'] = ActivityService::getInfo($id);
		}
		return $this->display('activity-info',$data);
	}
	
    public function postEdit()
	{
		$input['id'] = Input::get('id');
		$input['title'] = Input::get('title');
		$input['is_show'] = (int)Input::get('is_show',0);
		$input['starttime'] = strtotime(Input::get('starttime'));
		$input['endtime'] = strtotime(Input::get('endtime'));
		$input['share_times'] = (int)Input::get('share_times',0);
		$input['need_click_times'] = (int)Input::get('need_click_times',0);
		$id = ActivityService::saveInfo($input);
		if($id){
			return $this->redirect('wxshare/activity/list','活动保存成功');
		}else{
			return $this->back('保存失败');
		}
	}
	
	public function getReport()
	{
		$search = Input::only('activity_id','uid');
		$sort = Input::get('sort','id');
		$search['sort'] = $sort;
		$data = array();
		$pageIndex = Input::get('page',1);
		$pageSize = 10;
		$activitys = ActivityService::getAllActivityToKV();
		$data['activitys'] = $activitys;
		$data['search_activitys'] = array('0'=>'所有活动')+$activitys;
		$data['sort'] = $sort;
		$result = ActivityService::searchUserActivity($search,$pageIndex,$pageSize,array($sort=>'desc'));
		$uids = array();
		foreach($result['result'] as $row){
			$uids[] = $row['uid'];
		}
		if($uids){
		$data['users'] = UserService::getBatchUserInfo($uids);
		}
		
		$pager = Paginator::make(array(),$result['totalCount'],$pageSize);				
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		
		$data['datalist'] = $result['result'];
		return $this->display('activity-user-report',$data);
	}
	
    public function getHistory($user_activity_id)
	{
		$search = array('user_activity_id'=>$user_activity_id);
		$data = array();
		$pageIndex = Input::get('page',1);
		$pageSize = 10;
		$result = ActivityService::searchUserActivityHistory($search,$pageIndex,$pageSize);
		$data['datalist'] = $result['result'];
		$pager = Paginator::make(array(),$result['totalCount'],$pageSize);				
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		return $this->display('activity-user-history',$data);
	}
}