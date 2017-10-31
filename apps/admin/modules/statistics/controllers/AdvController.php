<?php
namespace modules\statistics\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;

use Youxiduo\Android\Model\AppAdvStat;

class AdvController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'statistics';
	}
	
	public function getUser()
	{
		$data = array();
		return $this->display('adv-user',$data);
	}
	
	public function postUser()
	{
		$aid = Input::get('aid');
	    $start = Input::get('startdate');
		$end = Input::get('enddate');
		$pageSize = (int)Input::get('pageSize',100);
		$export = (int)Input::get('export',0);
		if($pageSize<1) $pageSize = 100;
		if(!$start) {
			//$start = date('Y-m-d 00:00:00');
		}else{
			$start = $start . ' 00:00:00';
		}
		if(!$end) {
			//$end = date('Y-m-d 23:59:59');
		}else{
			$end = $end . ' 23:59:59';
		}
		$data['aid'] = $aid;
		$data['pageSize'] = $pageSize;
		//
		$tb = AppAdvStat::db()->where('aid','=',$aid);
		if($start){
			$tb = $tb->where('addtime','>=',strtotime($start));
		}
		if($end){
			$tb = $tb->where('addtime','<',strtotime($end));
		}
		$result = $tb->forPage(1,$pageSize)->get();
		if($result){
			$out = array();
			foreach($result as $row){
				$out[] = array('idfa'=>$row['idfa'],'openudid'=>$row['openudid'],'number'=>$row['number'],'addtime'=>date('Y-m-d',$row['addtime']));
			}
			if($export==0){
				require_once base_path() . '/libraries/PHPExcel.php';
				$excel = new \PHPExcel();
				$excel->setActiveSheetIndex(0);
				$excel->getActiveSheet()->setTitle('广告点击报表');
				$excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$excel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
				$excel->getActiveSheet()->setCellValue('A1','idfa');
				$excel->getActiveSheet()->setCellValue('B1','openudid');
				$excel->getActiveSheet()->setCellValue('C1','点击量');
				$excel->getActiveSheet()->setCellValue('D1','时间');
				foreach($out as $index=>$row){
					$excel->getActiveSheet()->setCellValue('A'.($index+2),$row['idfa']);
					$excel->getActiveSheet()->setCellValue('B'.($index+2),$row['openudid']);
					$excel->getActiveSheet()->setCellValue('C'.($index+2),$row['number']);
					$excel->getActiveSheet()->setCellValue('D'.($index+2),$row['addtime']);
				}
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		        header('Content-Disposition: attachment;filename="'. date('Y-m-d').'.xlsx"');
		        header('Cache-Control: max-age=0');
				$writer = \PHPExcel_IOFactory::createWriter($excel,'Excel2007');
				
				$writer->save('php://output');
				return $this->back('数据导出成功');
			}else{
				$data['datalist'] = $out;
				return $this->display('adv-user',$data);
			}
		    
		}else{
			return $this->back('数据不存在');
		}
		
	}
}