<?php
namespace modules\statistics\controllers;

use Yxd\Services\StatisticsService;
use modules\user\models\UserModel;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;

class ForumController extends RankController{
	protected static $search_param = array();
	
	public function _initialize(){
		$this->current_module = 'statistics';
	}
	
	public function getIndex(){
		$page = Input::get('page',1);
		$pagesize = 10;
		$date_type = Input::get('date_type','day');
		$time_area = array();
		switch ($date_type){
			case 'day':
				$time_area = $this->getDayTimeArea();
				break;
			case 'week':
				$time_area = $this->getWeekTimeArea();
				break;
			case 'month':
				$time_area = $this->getMonthTimeArea();
		}
		
		$sort = Input::get('sort','postf');
		$data = array();
		$weeks = $this->get_week(2014);
		foreach($weeks as $key=>$row){
			$weeks[$key] = '第'.$key.'周' . $row[0] . '-' . $row[1];
		}
		$data['weeks'] = $weeks;
		$data['month'] = 0;
		$data['months'] = $this->get_months();
		if($sort == 'postf'){
			$result = $this->getPostList($time_area['start'],$time_area['end'],$page,$pagesize);
		}else{
			$result = $this->getReplyList($time_area['start'],$time_area['end'],$page,$pagesize);
		}
		$data['count'] = $result['count'];
		$data['list'] = $result['list'];
		$data['bans'] = UserModel::getBanList();
		$data['usergroups'] = UserModel::getGroupNameList();
		self::$search_param['sort'] = $sort;
		$data['search'] = self::$search_param;
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$data['pagelinks'] = $pager->appends($data['search'])->links();
		return $this->display('forum-list',$data);
	}
	
	protected function getDayTimeArea(){
		$startdate = Input::get('startdate',date('Y-m-d'));
		$enddate = Input::get('enddate',date('Y-m-d'));
		$start = strtotime($startdate);
		$end   = strtotime($enddate) + 3600*24;
		self::$search_param['startdate'] = $startdate;
		self::$search_param['enddate'] = $enddate;
		return array('start'=>$start,'end'=>$end);
	}
	
	protected function getWeekTimeArea(){
		$weeks = $this->get_week(2014);
		$week = Input::get('week',30);
		$sort = Input::get('sort','income');
		$start = strtotime($weeks[$week][0]);
		$end   = strtotime($weeks[$week][1]) + 3600*24;
		self::$search_param['week'] = $week;
		return array('start'=>$start,'end'=>$end);
	}
	
	protected function getMonthTimeArea(){
		$weeks = $this->get_week(2014);
		$month = Input::get('month',date('m'));
		$sort = Input::get('sort','income');
		$start = strtotime('2014-'.$month.'-1');
		$end   = strtotime('2014-'.$month.'-'.date('t',mktime(0,0,0,$month,1,2014))) + 3600*24;
		self::$search_param['month'] = $month;
		return array('start'=>$start,'end'=>$end);
	}
	
	protected function getPostList($start_time,$end_time,$page,$pagesize){
		$count = StatisticsService::getPostCount($start_time, $end_time);
		$total = StatisticsService::getPostRankListCount($start_time, $end_time);
		$list = StatisticsService::getPostRankList($start_time, $end_time,$page,$pagesize);
		return array('count'=>$count,'total'=>$total,'list'=>$list);
	}
	
	protected function getReplyList($start_time,$end_time,$page,$pagesize){
		$count = StatisticsService::getReplyCount($start_time, $end_time);
		$total = StatisticsService::getReplyRankListCount($start_time, $end_time);
		$list = StatisticsService::getReplyRankList($start_time, $end_time,$page,$pagesize);
		return array('count'=>$count,'total'=>$total,'list'=>$list);
	}
}