<?php
namespace modules\activity\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;

use Yxd\Modules\Core\BackendController;

use Youxiduo\V4\Activity\Model\Report;
use Youxiduo\V4\Activity\Model\ChannelClick;
use Youxiduo\V4\Activity\Model\DownloadChannel;
use Youxiduo\V4\Activity\Model\StatisticConfig;
use Yxd\Services\UserService;

class ReportController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'activity';
	}
	
	public function getIndex()
	{
		return $this->display('report-list');
	}
	
	public function getShare()
	{
		return $this->display('report-list');
	}
	
	public function postExport()
	{
		$start = Input::get('startdate');
		$end = Input::get('enddate');
		$pageSize = (int)Input::get('pageSize',100);
		$export = (int)Input::get('export',0);
		if($pageSize<1) $pageSize = 100;
		if(!$start) {
			$start = date('Y-m-d 00:00:00');
		}else{
			$start = $start . ' 00:00:00';
		}
		if(!$end) {
			$end = date('Y-m-d 23:59:59');
		} else{
			$end = $end . ' 23:59:59';
		}
		$config_ids = StatisticConfig::db()->where('CHANNEL_ID','=','ZT_CHANNEL')->lists('CONFIG_ID');
		$result = ChannelClick::db()
		->whereIn('CONFIG_ID',$config_ids)
		->where('ACTIVE_TIME','>=',$start)
		->where('ACTIVE_TIME','<=',$end)
		->groupBy('CONFIG_ID')
		->select(ChannelClick::raw('CONFIG_ID,COUNT(*) AS TOTAL'))
		->orderBy('TOTAL','DESC')
		->forPage(1,$pageSize)		
		->get();
		//print_r($result);exit;
		if($result && is_array($result)){
			$out = array();
			$uids = array();
			foreach($result as $row){
				$uid = str_replace('zt_375_','',$row['CONFIG_ID']);
				$out[] = array('uid'=>$uid,'total'=>$row['TOTAL'],'config_id'=>$row['CONFIG_ID']);
				$uids[] = $uid;
			}
			$users = UserService::getBatchUserInfo($uids);
			if($export==0){
				require_once base_path() . '/libraries/PHPExcel.php';
				$excel = new \PHPExcel();
				$excel->setActiveSheetIndex(0);
				$excel->getActiveSheet()->setTitle('活动激活报表');
				$excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$excel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
				$excel->getActiveSheet()->setCellValue('A1','用户UID');
				$excel->getActiveSheet()->setCellValue('B1','用户昵称');
				$excel->getActiveSheet()->setCellValue('C1','激活量');
				foreach($out as $index=>$row){
					$excel->getActiveSheet()->setCellValue('A'.($index+2),$row['uid']);
					$excel->getActiveSheet()->setCellValue('B'.($index+2),isset($users[$row['uid']]) ? $users[$row['uid']]['nickname'] : '');
					$excel->getActiveSheet()->setCellValue('C'.($index+2),$row['total']);
				}
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		        header('Content-Disposition: attachment;filename="'. date('Y-m-d').'.xlsx"');
		        header('Cache-Control: max-age=0');
				$writer = \PHPExcel_IOFactory::createWriter($excel,'Excel2007');
				
				$writer->save('php://output');
				return $this->back('数据导出成功');
			}else{
				$data['datalist'] = $out;
				$data['users'] = $users;
			    return $this->display('report-list',$data);
			}
		}
		return $this->back('没有数据');
	}
	
	public function getList($config_id)
	{
		$result = ChannelClick::db()
		->where('CONFIG_ID','=',$config_id)
		->where('IS_ACTIVE','=',1)
		->orderBy('CLICK_ID','DESC')	
		->get();
		$uids = array();
		foreach($result as $row){
			$uids[] = $row['USER_ID'];
		}
		$users = UserService::getBatchUserInfo($uids);
		$out = array();
		foreach($result as $row){
			$nickname = isset($users[$row['USER_ID']]) ? $users[$row['USER_ID']]['nickname'] : '';
			$dateline = isset($users[$row['USER_ID']]) ? date('Y-m-d H:i:s',$users[$row['USER_ID']]['dateline']) : '';
			$out[] = array('uid'=>$row['USER_ID'],'nickname'=>$nickname,'device'=>$row['USER_DEVICE_KEY'],'regtime'=>$dateline);
		}
		$data = array();
		$data['datalist'] = $out;
		return $this->display('report-user',$data);
	}
}