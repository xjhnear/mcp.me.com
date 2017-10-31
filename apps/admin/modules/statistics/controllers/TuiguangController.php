<?php
namespace modules\statistics\controllers;

use Yxd\Services\CreditService;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;
use Yxd\Services\UserService;
use modules\user\models\UserModel;
use modules\statistics\models\RankModel;
use Yxd\Utility\ImageHelper;
use Yxd\Services\PassportService;
use PHPImageWorkshop\ImageWorkshop;
use modules\statistics\models\TuiguangModel;
use Yxd\Modules\System\SettingService;

class TuiguangController extends BackendController{
	public function _initialize()
	{
		$this->current_module = 'statistics';
	}
	
	public function getIndex()
	{
		return $this->getDay();		
	}
	
	public function getList($uid)
	{
		$page = Input::get('page', 1);
		$pagesize = 10;
		$data['datalist'] = TuiguangModel::getList($uid, $page, $pagesize);
		$totalcount = TuiguangModel::getAll($uid);
		$pager = Paginator::make(array(),$totalcount,$pagesize);
		$data['pagelinks'] = $pager->links();
		return $this->display('recommeduser-list', $data);
	}
	
 	public static function get_week($year = '2014')
	{
		$year_start = $year . '-01-01';
		$year_end = $year . '-12-31';
		$startday = strtotime($year_start);
		if(intval(date('N',$startday)) != 1)
		{
			$startday = strtotime("last monday", strtotime($year_start));
		}
		//第一周开始日期
		$year_mondy = date("Y-m-d", $startday);
		
		$endday = strtotime($year_end); 
		
		if (intval(date('N', $endday)) != 7) 
		{   
            $endday = strtotime("last sunday", strtotime($year_end));   
        }   
                
        $num = intval(date('W', $endday));
        
        for ($i = 1; $i <= $num; $i++) 
        {   
            $j = $i -1;   
            $start_date = date("Y-m-d", strtotime("$year_mondy $j week "));      
            $end_day = date("Y-m-d", strtotime("$start_date +6 day"));   
            $week_array[$i] = array($start_date, $end_day);   
        } 
        return $week_array;
	}

	public function getDay()
	{
		$page = Input::get('page', 1);
		$pagesize = 10;
		$startdate = Input::get('startdate',date('Y-m-d'));
		$enddate = Input::get('enddate',date('Y-m-d'));
		$sort = Input::get('sort','income');
		$start = strtotime($startdate);
		$end   = strtotime($enddate) + 3600*24;
		$data = array();
		$weeks = $this->get_week(2014);
		foreach($weeks as $key=>$row){
			$weeks[$key] = '第'.$key.'周' . $row[0] . '-' . $row[1];
		}
		$data['weeks'] = $weeks;
		$data['month'] = 0;
		$data['months'] = $this->get_months();
		$data['search'] = array('startdate'=>$startdate,'enddate'=>$enddate);
		$oldids = TuiguangModel::getTuiguangIds($start, $end);
		$data['datalist'] = TuiguangModel::getTuiguangList($oldids, $page, $pagesize);
		$totalcount = TuiguangModel::getAllcount($oldids);
		$pager = Paginator::make(array(),$totalcount,$pagesize);
	    $data['pagelinks'] = $pager->links();
		return $this->display('tuiguang-list',$data);
	}
	
	public function getWeek()
	{
		$page = Input::get('page', 1);
		$pagesize = 1;
		$weeks = $this->get_week(2014);
		$week = Input::get('week',30);
		$start = strtotime($weeks[$week][0]);
		$end   = strtotime($weeks[$week][1]) + 3600*24;
		$data = array();
		
		foreach($weeks as $key=>$row){
			$weeks[$key] = '第'.$key.'周' . $row[0] . '-' . $row[1];
		}
		$data['weeks'] = $weeks;
		$data['months'] = array();
		$oldids = TuiguangModel::getTuiguangIds($start, $end);
		$data['datalist'] = TuiguangModel::getTuiguangList($oldids, $page, $pagesize);
		$data['week'] = $week;
		$data['month'] = 0;
		$data['months'] = $this->get_months();
		$totalcount = TuiguangModel::getAllcount($oldids);
		$pager = Paginator::make(array(),$totalcount,$pagesize);
	    $data['pagelinks'] = $pager->links();
		return $this->display('tuiguang-list',$data);
	}
	
	public function getMonth()
	{
		
		$page = Input::get('page', 1);
		$pagesize = 10;
		$weeks = $this->get_week(2014);
		$month = Input::get('month',date('m'));
		$start = strtotime('2014-'.$month.'-1');
		$end   = strtotime('2014-'.$month.'-'.date('t',mktime(0,0,0,$month,1,2014))) + 3600*24;
		$data = array();
		foreach($weeks as $key=>$row){
			$weeks[$key] = '第'.$key.'周' . $row[0] . '-' . $row[1];
		}
		$data['weeks'] = $weeks;
		$data['months'] = array();
		$oldids = TuiguangModel::getTuiguangIds($start, $end);
		$data['datalist'] = TuiguangModel::getTuiguangList($oldids, $page, $pagesize);
		$data['month'] = $month;
		$data['months'] = $this->get_months();
		$totalcount = TuiguangModel::getAllcount($oldids);
		$pager = Paginator::make(array(),$totalcount,$pagesize);
	    $data['pagelinks'] = $pager->links();
		return $this->display('tuiguang-list',$data);
	}
	
	protected function get_months()
	{
		$months = array();
		for($i=1;$i<=12;$i++){
			$months[$i] = $i . '月';
		}
		return $months;
	}
		
}