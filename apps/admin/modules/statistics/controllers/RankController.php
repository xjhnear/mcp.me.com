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
use Youxiduo\Base\AllService;

use PHPImageWorkshop\ImageWorkshop;

class RankController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'statistics';
	}
	
	public function getIndex()
	{
		return $this->getDay();		
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
		if($sort=='income'){
		    $data['datalist'] = RankModel::getMoneyIncomeList($start, $end);
		}else{
			$data['datalist'] = RankModel::getMoneyExpendList($start, $end);
		}
		$data['bans'] = UserModel::getBanList();
		$data['total_income'] = (int)RankModel::getMoneyIncomeSum($start, $end);
		$data['total_expend'] = (int)RankModel::getMoneyExpendSum($start, $end);
		$data['search'] = array('startdate'=>$startdate,'enddate'=>$enddate);
		$data['sort'] = $sort;
		return $this->display('credit-list',$data);
	}
	
	public function getWeek()
	{
		$weeks = $this->get_week(2014);
		$week = Input::get('week',30);
		$sort = Input::get('sort','income');
		$start = strtotime($weeks[$week][0]);
		$end   = strtotime($weeks[$week][1]) + 3600*24;
		$data = array();
		
		foreach($weeks as $key=>$row){
			$weeks[$key] = '第'.$key.'周' . $row[0] . '-' . $row[1];
		}
		$data['weeks'] = $weeks;
		$data['months'] = array();
	    if($sort=='income'){
		    $data['datalist'] = RankModel::getMoneyIncomeList($start, $end);
		}else{
			$data['datalist'] = RankModel::getMoneyExpendList($start, $end);
		}
		$data['bans'] = UserModel::getBanList();
		$data['total_income'] = (int)RankModel::getMoneyIncomeSum($start, $end);
		$data['total_expend'] = (int)RankModel::getMoneyExpendSum($start, $end);
		$data['week'] = $week;
		$data['month'] = 0;
		$data['months'] = $this->get_months();
		$data['sort'] = $sort;
		return $this->display('credit-list',$data);
	}
	
	public function getMonth()
	{
		$weeks = $this->get_week(2014);
		$month = Input::get('month',date('m'));
		$sort = Input::get('sort','income');
		$start = strtotime('2014-'.$month.'-1');
		$end   = strtotime('2014-'.$month.'-'.date('t',mktime(0,0,0,$month,1,2014))) + 3600*24;
		$data = array();
		foreach($weeks as $key=>$row){
			$weeks[$key] = '第'.$key.'周' . $row[0] . '-' . $row[1];
		}
		$data['weeks'] = $weeks;
		$data['months'] = array();
	    if($sort=='income'){
		    $data['datalist'] = RankModel::getMoneyIncomeList($start, $end);
		}else{
			$data['datalist'] = RankModel::getMoneyExpendList($start, $end);
		}
		$data['bans'] = UserModel::getBanList();
		$data['total_income'] = (int)RankModel::getMoneyIncomeSum($start, $end);
		$data['total_expend'] = (int)RankModel::getMoneyExpendSum($start, $end);
		$data['month'] = $month;
		$data['months'] = $this->get_months();
		$data['sort'] = $sort;
		return $this->display('credit-list',$data);
	}
	
	protected function get_months()
	{
		$months = array();
		for($i=1;$i<=12;$i++){
			$months[$i] = $i . '月';
		}
		return $months;
	}
	
	//游币发放与消耗
	public function getMoneyConsume()
	{
	    $data = $search = array();
	    $data['give'] = '系统发放';
	    $data['use'] = '系统消耗';
	    $data['person'] = '人工发放';
	    $data['consume'] = '人工消耗';
	    $search = Input::get();
	    $search['platform'] = 'android';
	    $search['timeBegin'] = isset($search['startDate'])?$search['startDate']:0;
	    $search['timeEnd'] = isset($search['endDate'])?$search['endDate']:0;
	    $search = array_filter($search);
	    $search['currencyType'] = '0';
	    $search['operationType'] = 'sys_operation';
	    $search['isPositive'] = 'true';
	    $res = AllService::excute2("48080",$search,"module_rmb/account/currencyStatics");
	    if($res['success'])
	        $data['xt'] = $res['data'];
	    $search['isPositive'] = 'false';
	    $res = AllService::excute2("48080",$search,"module_rmb/account/currencyStatics");
	    if($res['success'])
	        $data['xh'] = $res['data'];
	    unset($search['operationType']);
	    $search['operationType'] = 'manage ';
	    $search['isPositive'] = 'true';
	    $res = AllService::excute2("48080",$search,"module_rmb/account/currencyStatics");
	    if($res['success'])
	        $data['rg'] = $res['data'];
	    $search['isPositive'] = 'false';
	    $res = AllService::excute2("48080",$search,"module_rmb/account/currencyStatics");
	    if($res['success'])
	        $data['rgxh'] = $res['data'];
	    $data['search'] = $search;
	    return $this->display('appaccount-ybf-list',$data);
	}
	
	
}