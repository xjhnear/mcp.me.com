<?php
namespace modules\giftbag\controllers;

use modules\giftbag\models\GiftbagModel;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;

use Yxd\Modules\Core\BackendController;
use modules\giftbag\models\GiftCardModel;
use Yxd\Modules\Activity\GiftbagService;

class GiftCardController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'giftbag';
	}
	
	/**
	 * 礼包导入界面
	 */
	public function getImport($giftbag_id)
	{
		$data = array();
		$data['gift'] = GiftbagModel::getInfo($giftbag_id);
		return $this->display('giftcard-import',$data);
	}
	
	/**
	 * 礼包导入
	 */	
	public function postImport()
	{
		$giftbag_id = Input::get('giftbag_id');
		$type_repeat = (int)Input::get('type_repeat',0);
		$gift = GiftbagModel::getInfo($giftbag_id);
		if(!$gift){
			//
			return $this->back()->with('global_tips','礼包不存在');
		}
		if(!Input::hasFile('filedata')){
			return $this->back()->with('global_tips','礼包卡文件不存在');
		}
		$file = Input::file('filedata');
		$tmpfile = $file->getRealPath();
		$filename = $file->getClientOriginalName();
		$ext = $file->getClientOriginalExtension();				
		if(!in_array($ext,array('xls','xlsx','csv','txt'))) return $this->back()->with('global_tips','上传文件格式错误');
		$server_path = storage_path() . '/tmp/'; 
		$newfilename = microtime() . '.' . $ext;
		$target = $server_path . $newfilename;
		$file->move($server_path,$newfilename);
		$card = array();
		if($ext == 'txt'){
			$fp = fopen($target, 'r');
	        $line = 1;                
            while (!feof($fp)) {
                $row = trim(fgets($fp));
                if (strlen($row) < 1) {
                	continue;                        
                }
                $line++;
                $card[] = $row;
            }   
		}else{
			require_once base_path() . '/libraries/PHPExcel.php';
			$excel = \PHPExcel_IOFactory::load($target);
			$arrExcel = $excel->getSheet(0)->toArray();
			
		    if($arrExcel){
				//$patterns = "/[^\d\w]+/";
				$patterns = "/[^a-zA-Z0-9_-]+/";
				foreach ($arrExcel as $v){
					if(empty($v[0])){
						continue;
					}
					//只保留数字和字母
					$_temp = preg_replace($patterns, "", $v[0]);
					$card[] = $_temp;				
				}			
			}
		}
		if(!$card || empty($card)) return $this->back()->with('global_tips','礼包卡无效');
		$new_card = array();
		$exists_card = array();
		
		if($type_repeat==1){
			$card = array_unique($card);
			$exists_card = GiftCardModel::getCardNoList($giftbag_id);
			$new_card = array_diff($card,$exists_card);
		}else{
			$new_card = $card;
		}
		if(!$new_card) return $this->back()->with('global_tips','礼包卡无效');
		$result = GiftCardModel::importCardNo($new_card, $giftbag_id);
		if($result){
			@unlink($target);
			return $this->redirect('giftbag/giftcard/list/' . $giftbag_id)->with('global_tips','礼包卡导入成功');
		}else{
			return $this->back()->with('global_tips','礼包卡导入失败');
		}
	}
	
	public function getDelete($id)
	{
		GiftCardModel::delete($id);				
		return $this->back()->with('global_tips','礼包卡删除成功');
	}
	
	public function getInitQueue($giftbag_id)
	{
		 $giftbag_id && GiftbagService::initGiftbagCardNoQueue($giftbag_id);
		 return $this->back('礼包领取队列初始化成功');
	}
	
	public function getList($giftbag_id)
	{
		$data = array();
		$search = array('giftbag_id'=>$giftbag_id);
		$page = Input::get('page',1);
		$pagesize = 10;
		$result = GiftCardModel::search($search,$page,$pagesize,array('gettime'=>'desc','id'=>'desc'));
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $result['total'];
		$data['datalist'] = $result['result'];	
		return $this->display('giftcard-list',$data);
	}
	
	public function getExportCard()
	{
		$giftbag_id = Input::get('giftbag_id');
		if(!$giftbag_id) return $this->back('礼包参数错误');
		$number = (int)Input::get('number');
		if($number<=0) return $this->back('卡号数量必须大于0');
		$result = GiftCardModel::exportCardNo($giftbag_id, $number);
		$out = array();
		foreach($result as $row){
			$out[] = array('cardno'=>$row['cardno']);
		}
		require_once base_path() . '/libraries/PHPExcel.php';
		$excel = new \PHPExcel();
		$excel->setActiveSheetIndex(0);
		$excel->getActiveSheet()->setTitle('礼包领取报表');
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
		$excel->getActiveSheet()->setCellValue('A1','卡号');
		foreach($out as $index=>$row){
			$excel->getActiveSheet()->setCellValue('A'.($index+2),$row['cardno']);
		}
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'. date('Y-m-d').'.xlsx"');
        header('Cache-Control: max-age=0');
		$writer = \PHPExcel_IOFactory::createWriter($excel,'Excel2007');
		
		$writer->save('php://output');
		
		return $this->back();
	}
	
	public function getExport($giftbag_id)
	{
		$result = GiftCardModel::exportUserCardNo($giftbag_id,1);
		if(!$result) return $this->back('数据不存在');
		$out = array();
		foreach($result as $row){
			$out[] = array('cardno'=>$row['cardno'],'uid'=>$row['uid'],'gettime'=>date('Y-m-d H:i:s',$row['gettime']));
		}
		require_once base_path() . '/libraries/PHPExcel.php';
		$excel = new \PHPExcel();
		$excel->setActiveSheetIndex(0);
		$excel->getActiveSheet()->setTitle('礼包领取报表');
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
		$excel->getActiveSheet()->setCellValue('A1','用户UID');
		$excel->getActiveSheet()->setCellValue('B1','领取的卡号');
		$excel->getActiveSheet()->setCellValue('C1','领取时间');
		foreach($out as $index=>$row){
			$excel->getActiveSheet()->setCellValue('A'.($index+2),$row['uid']);
			$excel->getActiveSheet()->setCellValue('B'.($index+2),$row['cardno']);
			$excel->getActiveSheet()->setCellValue('C'.($index+2),$row['gettime']);
		}
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'. date('Y-m-d').'.xlsx"');
        header('Cache-Control: max-age=0');
		$writer = \PHPExcel_IOFactory::createWriter($excel,'Excel2007');
		
		$writer->save('php://output');
		
		return $this->back();
	}

	public function getExportRank($giftbag_id)
	{
		$result = GiftbagModel::dbClubMaster()->table('gift_account')
			->select(\DB::Raw('uid,count(*) as total'))
			->where('gift_id','=',$giftbag_id)
			->groupBy('uid')
			->orderBy('total','desc')
			->forPage(1,50)
			->get();

		require_once base_path() . '/libraries/PHPExcel.php';
		$excel = new \PHPExcel();
		$excel->setActiveSheetIndex(0);
		$excel->getActiveSheet()->setTitle('礼包领取报表');
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
		$excel->getActiveSheet()->setCellValue('A1','用户UID');
		$excel->getActiveSheet()->setCellValue('B1','领取数量');
		foreach($result as $index=>$row){
			$excel->getActiveSheet()->setCellValue('A'.($index+2),$row['uid']);
			$excel->getActiveSheet()->setCellValue('B'.($index+2),$row['total']);
		}
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'. date('Y-m-d').'.xlsx"');
		header('Cache-Control: max-age=0');
		$writer = \PHPExcel_IOFactory::createWriter($excel,'Excel2007');

		$writer->save('php://output');

		return $this->back();
	}
}