<?php
namespace modules\statistics\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;

use Youxiduo\V4\Activity\Model\ChannelClick;
use Youxiduo\V4\Activity\Model\DownloadChannel;
use Youxiduo\V4\Activity\Model\StatisticConfig;
use Yxd\Services\UserService;

use modules\statistics\models\MonitorService;

class MonitorController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'statistics';
	}
	
	public function getChannelList()
	{
		$pageIndex = Input::get('page',1);
		$pageSize = 10;
		$data = array();
		$channel_name = Input::get('channel_name','');
		$search['channel_name'] = $channel_name;
		$result = MonitorService::searchChannel($search,$pageIndex,$pageSize);
		$pager = Paginator::make(array(),$result['total'],$pageSize);
		$pager->appends($search);
		$data['pagelinks'] = $pager->links();
		$data['datalist'] = $result['result'];
		$data['search'] = $search;
		return $this->display('monitor-channel-list',$data);
	}
	
	public function getChannelEdit()
	{
		$data = array();
		$data['channel'] = array('IS_ACTIVE'=>1);
		return $this->display('monitor-channel-info',$data);
	}
	
	public function postChannelEdit()
	{
		$channel_id = Input::get('channel_id','');
		$channel_name = Input::get('channel_name','');
		$is_active = Input::get('is_active',0) ? true : false;
		if(empty($channel_id)) return $this->back('渠道标识不能为空');
		if(empty($channel_name)) return $this->back('渠道名称不能为空');
		
		$exists = MonitorService::isExistsChannel($channel_id);
		if($exists==true) return $this->back('渠道标识不能重复');
		$data = array('CHANNEL_ID'=>$channel_id,'CHANNEL_NAME'=>$channel_name,'IS_ACTIVE'=>$is_active,'CREATE_TIME'=>date('Y-m-d H:i:s'));
		DownloadChannel::db()->insert($data);
		//$success = MonitorService::createChannel($channel_id,$channel_name,$is_active);
		//if(!$success) return $this->back('渠道创建失败');
		return $this->redirect('statistics/monitor/channel-list','渠道新建成功');
	}
	
	public function getConfigList()
	{
		$pageIndex = Input::get('page',1);
		$pageSize = 10;
		$channel_id = Input::get('channel_id','');
		$config_id = Input::get('config_id','');
		$config_name = Input::get('config_name','');
		$data = array();
		$data['channel_list'] = DownloadChannel::db()->lists('CHANNEL_NAME','CHANNEL_ID');
		
		$search['config_name'] = $config_name;
		$search['config_id'] = $config_id;
		$search['channel_id'] = $channel_id;
		
		$result = MonitorService::searchConfig($search,$pageIndex,$pageSize);
		$pager = Paginator::make(array(),$result['total'],$pageSize);
		$pager->appends($search);
		$data['pagelinks'] = $pager->links();
		$data['datalist'] = $result['result'];
		$data['search'] = $search;
		
		return $this->display('monitor-config-list',$data);
	}
	
	public function getConfigEdit($config_id='')
	{
		$channel_id = Input::get('channel_id','');
		
		$data = array();
		$data['os_list'] = array('IOS'=>'苹果','ANDROID'=>'安卓');
		$data['channel_list'] = DownloadChannel::db()->lists('CHANNEL_NAME','CHANNEL_ID');
		if($config_id){
			$data['config'] = StatisticConfig::db()->where('CONFIG_ID','=',$config_id)->first();
		}else{
			$data['config'] = array('CHANNEL_ID'=>$channel_id);
		}
		return $this->display('monitor-config-info',$data);
	}
	
	public function postConfigEdit()
	{
		$input = Input::only('config_id','channel_id','redirect_url','config_name','config_os','click_call_back_url');		
		if(!$input['config_id']) return $this->back('配置标识不能为空');
		if(!$input['config_name']) return $this->back('配置名称不能为空');
		if(!$input['redirect_url']) return $this->back('跳转URL不能为空');
		$exists = StatisticConfig::db()->where('CONFIG_ID','=',$input['config_id'])->first();	
		$config_id = $input['config_id'];
		$config_name = $input['config_name'];
		$config_os = $input['config_os'];
		$channel_id = $input['channel_id'];
		$redirect_url = $input['redirect_url'];	
		$click_call_back_url = $input['click_call_back_url'];
		$exists = MonitorService::isExistsConfig($config_id);
		if($exists) return $this->back('配置标识不能重复');
		$data = array('CONFIG_ID'=>$input['config_id'],'CONFIG_NAME'=>$input['config_name'],'CONFIG_OS'=>$input['config_os'],'CHANNEL_ID'=>$input['channel_id'],'REDIRECT_URL'=>$input['redirect_url'],'CREATE_TIME'=>date('Y-m-d H:i:s'));
		StatisticConfig::db()->insert($data);
		//$success = MonitorService::createConfig($config_id,$config_name,$config_os,$redirect_url,$channel_id,$click_call_back_url,true);
		//if(!$success) return $this->back('配置创建失败');
		return $this->redirect('statistics/monitor/config-list?channel_id='.$input['channel_id'],'配置新建成功');
	}
	
	public function getClickList()
	{
		$data = array();
		$config_id = Input::get('config_id');
		$channel_id = Input::get('channel_id');
	    $start = Input::get('startdate');
		$end = Input::get('enddate');		
		$pageIndex = (int)Input::get('page',1);
		$pageSize = 10;
		$export = (int)Input::get('export');
		if($pageSize<1) $pageSize = 100;
		if($start) {
			$start = $start . ' 00:00:00';
		}
		if($end) {
			$end = $end . ' 23:59:59';
		}
		$search = array();
	    $search['config_id'] = $config_id;
	    $search['channel_id'] = $channel_id;
	    $search['start'] = $start;
		$search['end'] = $end;
		if($export==1){
			
			$pageSize = (int)Input::get('pageSize',100);
			$result = MonitorService::searchClick($search,1,$pageSize,'CLICK_COUNT');
			if(!$result['result']) return $this->back('数据不存在');
			$out = array();
			foreach($result['result'] as $row){
				$out[] = array('click_ip'=>$row['CLICK_IP'],'click_count'=>$row['CLICK_COUNT'],'click_time'=>$row['CLICK_TIME']);
			}
			require_once base_path() . '/libraries/PHPExcel.php';
			$excel = new \PHPExcel();
			$excel->setActiveSheetIndex(0);
			$excel->getActiveSheet()->setTitle('点击报表');
			$excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
			$excel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
			$excel->getActiveSheet()->setCellValue('A1','IP');
			$excel->getActiveSheet()->setCellValue('B1','点击量');
			$excel->getActiveSheet()->setCellValue('C1','时间');
			foreach($out as $index=>$row){
				$excel->getActiveSheet()->setCellValue('A'.($index+2),$row['click_ip']);
				$excel->getActiveSheet()->setCellValue('B'.($index+2),$row['click_count']);
				$excel->getActiveSheet()->setCellValue('C'.($index+2),$row['click_time']);
			}
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	        header('Content-Disposition: attachment;filename="'. date('Y-m-d').'.xlsx"');
	        header('Cache-Control: max-age=0');
			$writer = \PHPExcel_IOFactory::createWriter($excel,'Excel2007');
			
			$writer->save('php://output');
			return $this->back();
		}else{
			$result = MonitorService::searchClick($search,$pageIndex,$pageSize,'CLICK_COUNT');
			$pager = Paginator::make(array(),$result['total'],$pageSize);
			$pager->appends($search);
			$data['pagelinks'] = $pager->links();
			$data['datalist'] = $result['result'];
			$data['search'] = $search;
			$data['total'] = $result['total'];
			return $this->display('monitor-click-list',$data);
		}				
	}
	
	public function getActiveList()
	{
	    $data = array();
		$config_id = Input::get('config_id');
		$channel_id = Input::get('channel_id');
	    $start = Input::get('startdate');
		$end = Input::get('enddate');		
		$pageIndex = (int)Input::get('page',1);
		$pageSize = 10;
		$export = (int)Input::get('export');
		if($pageSize<1) $pageSize = 100;
		if($start) {
			$start = $start . ' 00:00:00';
		}
		if($end) {
			$end = $end . ' 23:59:59';
		}
		$search = array();
		$search['config_id'] = $config_id;
		$search['channel_id'] = $channel_id;
		$search['start'] = $start;
		$search['end'] = $end;
		if($export==1){			
			$pageSize = (int)Input::get('pageSize',100);
			//
			$result = MonitorService::searchActive($search,1,$pageSize);
			if(!$result['result']) return $this->back('数据不存在');
			$out = array();
			foreach($result['result'] as $row){
				$out[] = array('click_ip'=>$row['CLICK_IP'],'click_count'=>$row['CLICK_COUNT'],'active_time'=>$row['ACTIVE_TIME'],'user_id'=>$row['USER_ID'],'user_key'=>$row['USER_KEY'],'user_device_key'=>$row['USER_DEVICE_KEY']);
			}
			require_once base_path() . '/libraries/PHPExcel.php';
			$excel = new \PHPExcel();
			$excel->setActiveSheetIndex(0);
			$excel->getActiveSheet()->setTitle('点击报表');
			$excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
			$excel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
			$excel->getActiveSheet()->setCellValue('A1','IP');
			$excel->getActiveSheet()->setCellValue('B1','点击量');
			$excel->getActiveSheet()->setCellValue('C1','激活时间');
			$excel->getActiveSheet()->setCellValue('D1','用户UID');
			$excel->getActiveSheet()->setCellValue('E1','账号');
			$excel->getActiveSheet()->setCellValue('F1','设备');
			
			foreach($out as $index=>$row){
				$excel->getActiveSheet()->setCellValue('A'.($index+2),$row['click_ip']);
				$excel->getActiveSheet()->setCellValue('B'.($index+2),$row['click_count']);
				$excel->getActiveSheet()->setCellValue('C'.($index+2),$row['active_time']);
				$excel->getActiveSheet()->setCellValue('D'.($index+2),$row['user_id']);
				$excel->getActiveSheet()->setCellValue('E'.($index+2),$row['user_key']);
				$excel->getActiveSheet()->setCellValue('F'.($index+2),$row['user_device_key']);
			}
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	        header('Content-Disposition: attachment;filename="'. date('Y-m-d').'.xlsx"');
	        header('Cache-Control: max-age=0');
			$writer = \PHPExcel_IOFactory::createWriter($excel,'Excel2007');
			
			$writer->save('php://output');
			return $this->back();
		}else{
			//
			$result = MonitorService::searchActive($search,$pageIndex,$pageSize);
			$pager = Paginator::make(array(),$result['total'],$pageSize);
			$pager->appends($search);
			$data['pagelinks'] = $pager->links();
			$data['datalist'] = $result['result'];
			$data['search'] = $search;
			$data['total'] = $result['total'];
			return $this->display('monitor-active-list',$data);
		}
	}
}