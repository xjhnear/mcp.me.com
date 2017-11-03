<?php
namespace modules\phone\controllers;

use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;

use Youxiduo\Phone\Model\PhoneBatch;
use Youxiduo\Phone\Model\PhoneNumbers;

class BatchController extends BackendController
{
	public function _initialize(){
		$this->current_module = 'phone';
	}
	
	public function getList()
	{
		$pageIndex = Input::get('page',1);
		$search = Input::only('batch_code');
		$pageSize = 10;
		$data = array();
		$data['datalist'] = PhoneBatch::getList($search,$pageIndex,$pageSize);
		$data['search'] = $search;
		$total = PhoneBatch::getCount($search);
		$pager = Paginator::make(array(),$total,$pageSize);
		$pager->appends($search);
		$data['pagelinks'] = $pager->links();
		return $this->display('batch_list',$data);
	}
	
	public function getAdd()
	{
		$data = array();
		return $this->display('batch_info',$data);
	}
	
	public function getEdit($batch_id)
	{
		$data = array();
		$data['info'] = PhoneBatch::getInfo($batch_id);
		return $this->display('batch_info',$data);
	}
	
	public function postSave()
	{
		$input = Input::only('batch_id','batch_code');

		$result = PhoneBatch::save($input);
		if($result){
			return $this->redirect('phone/batch/list','批次保存成功');
		}else{
			return $this->back('批次保存成功');
		}
	}

	public function getDel($batch_id=0)
	{
		if($batch_id){
			PhoneBatch::del($batch_id);
		}
		return $this->redirect('phone/batch/list','批次删除成功');
	}

	public function postAjaxUploadFile(){
		$batch_code = Input::get('batch_code');
		if(!Input::hasFile('append_file'))
			return json_encode(array('state'=>0,'msg'=>'文件不存在'));
		$file = Input::file('append_file');
		$tmpfile = $file->getRealPath();
		$filename = $file->getClientOriginalName();
		$ext = $file->getClientOriginalExtension();
		if(!in_array($ext,array('xls','xlsx','csv'))) return json_encode(array('state'=>0,'msg'=>'上传文件格式错误'));
		$server_path = storage_path() . '/tmp/';
		$newfilename = microtime() . '.' . $ext;
		$target = $server_path . $newfilename;
		$file->move($server_path,$newfilename);
		require_once base_path() . '/libraries/PHPExcel.php';

		$inputFileType = \PHPExcel_IOFactory::identify($target);
		$objReader = \PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setReadDataOnly(true);
		$excel = $objReader->load($target,$encode='utf-8');

		$arrExcel = $excel->getSheet(0)->toArray();

		$input = array();
		if($batch_code) {
			$info_exists = PhoneBatch::getInfoByCode($batch_code);
			if ($info_exists) {
				return json_encode(array('state'=>0,'msg'=>'批次Code已存在'));
			} else {
				$input['batch_code'] = $batch_code;
			}
		} else {
			return json_encode(array('state'=>0,'msg'=>'批次Code不能为空'));
		}
		$re_batch = PhoneBatch::save($input);

		array_shift($arrExcel);
		$i = 0;
		foreach ($arrExcel as $item) {
			$data_tmp = array();
			$data_tmp['batch_id'] = $re_batch;
			$data_tmp['phone_number'] = $item[0];
			$re_num = PhoneNumbers::save($data_tmp);
			if ($re_num) $i++;
		}

		$data = array();
		$data['batch_id'] = $re_batch;
		$data['count'] = $i;
		$res = PhoneBatch::save($data);

		if($res){
			return json_encode(array("state"=>1,'msg'=>'批次添加成功'));
		}else{
			return json_encode(array('state'=>0,'msg'=>'批次添加失败'));
		}
	}

	public function postAjaxDownFile(){
		ini_set('max_execution_time', '0');
		$batch_id = Input::get('batch_id');
		$pageSize = Input::get('pageSize');
		if(!$batch_id) return json_encode(array('state'=>0,'msg'=>'数据异常'));
		$info_batch = PhoneBatch::getInfo($batch_id);
		if(!$info_batch) return json_encode(array('state'=>0,'msg'=>'批次不存在'));
		$search = array();
		$search['batch_id'] = $info_batch['batch_id'];
		$batch_code = $info_batch['batch_code'];
		$pageIndex = 1;
		if ($pageSize > 0) {
			$info_num_count = PhoneNumbers::getCount($search);
			$pages = ceil($info_num_count/$pageSize);
		} else {
			$pages = 1;
		}
		while($pageIndex<=$pages) {
			$info_num = PhoneNumbers::getList($search,$pageIndex,$pageSize);
			if ($info_num) {
				require_once base_path() . '/libraries/PHPExcel.php';
				$excel = new \PHPExcel();
				$excel->setActiveSheetIndex(0);
				$excel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$excel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$excel->getActiveSheet()->setTitle($batch_code);
				$excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
				$excel->getActiveSheet()->setCellValue('A1','标题');
				$excel->getActiveSheet()->freezePane('A2');
				foreach($info_num as $index=>$row){
					$phone_number = isset($row['phone_number'])?$row['phone_number']:'';

					$excel->getActiveSheet()->setCellValue('A'.($index+2), $phone_number);

				}
				$filename = $batch_code .'--'. date('Ymd');
//			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//			header('Content-Disposition: attachment;filename="'. $filename.'.xlsx"');
//			header('Cache-Control: max-age=0');
//			$writer = \PHPExcel_IOFactory::createWriter($excel,'Excel2007');

				$writer = new \PHPExcel_Writer_Excel2007($excel);
				self::saveExcelToLocalFile($writer,$filename,$pageIndex);
			}
			$pageIndex++;
		}
		$zipname = $batch_code .'--'. date('Ymd');
		$zip = new \ZipArchive();
		if($zip->open(public_path().'/downloads/'.$zipname.'.zip', \ZipArchive::CREATE) === TRUE) {
			self::addFileToZip(public_path().'/downloads/'.$filename, $zip); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
			$zip->close(); //关闭处理的zip文件
		}
		$url = '/downloads/'.$zipname.'.zip';
		return json_encode(array('state'=>1,'url'=>$url));
	}

	/**
	 * excel导出
	 */
	public function getProductDataDownload()
	{
		$data = array();
		$uid = '';
		$pageSize = 2000;
		$taskId = '';
		$taskName = '';
		$pageIndex = Input::get('page',1);
		$stepId = Input::get('stepId','');
		$title = Input::get('title','');
		$stepStatus = Input::get('stepStatus','');
		$name = Input::get('name','');
		$add_user = Input::get('addUser','false');
		$isIssue = Input::get('isIssue','');
		if(!empty($name)){
			$arr_uid = array();
			$arr_uid_info = UserService::searchByUserName($name);
			foreach($arr_uid_info as $k =>$v){
				$arr_uid[$k] = $v['uid'];
			}
			$arr_uid_str = implode(',',$arr_uid);
			if(isset($arr_uid_str)){
				$uid = $arr_uid_str;
			}
		}
		$startTime = Input::get('startTime','');
		$endTime = Input::get('endTime','');
		$search = array('pageSize'=>$pageSize,'pageNow'=>$pageIndex,'stepId'=>$stepId,'startTime'=>$startTime,'endTime'=>$endTime,'uid'=>$name,'stepStatus'=>$stepStatus,'add_user'=>$add_user,'isIssue'=>$isIssue);
		$res = TaskLionService::task_checked(array_filter($search));
		if(!$res['errorCode']&&$res['result']){

			$total = $res['totalCount'];
			$result = $res['result'];
			$taskId = '';
			$taskName = '';
			$arr_res = $arr_info = array();
			foreach($result as $k=>$row){
				$taskId = $row['taskId'];
				$taskName = $row['taskName'];
				$result[$k]['userinfo']['uid'] = $row['uid'];

				if(isset($row['approvalContent'])&&!empty($row['approvalContent'])){
					if(is_null(json_decode($row['approvalContent']))){
						$result[$k]['autorContent'] = $row['approvalContent'];
					}else{
						$result[$k]['approvalContent'] = json_decode($row['approvalContent'],true);
						$arr = self::$approvalArr;
						foreach($result[$k]['approvalContent'] as $j => $v){
							if (is_array($v)) {
								$title = $v['title'];unset($v['title']);
								$result[$k]['approvalContent'][$title] = array_pop($v);
								unset($result[$k]['approvalContent'][$j]);
							} else {
								$result[$k]['approvalContent'][] = $result[$k]['approvalContent'][$j];
								unset($result[$k]['approvalContent'][$j]);
							}
						}
					}


					if(isset($row['prizeContent'])&&!empty($row['prizeContent'])){
						$re = explode(',', $row['prizeContent']);
						@$result[$k]['approvalContent']['用户选择'] = $re[0];
					}

					$result[$k]['approvalContent'] = json_encode($result[$k]['approvalContent']);
				}

			}
		}else{
			$total = 0;
			$result= array();
		}
		//var_dump($result);die;
		require_once base_path() . '/libraries/PHPExcel.php';
		$excel = new \PHPExcel();
		$excel->setActiveSheetIndex(0);
		$excel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$excel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$excel->getActiveSheet()->setTitle('任务审核用户统计');
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(100);
		$excel->getActiveSheet()->setCellValue('A1','任务标题');
		$excel->getActiveSheet()->setCellValue('B1','用户名');
		$excel->getActiveSheet()->setCellValue('C1','审核状态');
		$excel->getActiveSheet()->setCellValue('D1','上传截图时间');
		$excel->getActiveSheet()->setCellValue('E1','操作人员和时间');
		$excel->getActiveSheet()->setCellValue('F1','玩家上传信息');
		$excel->getActiveSheet()->freezePane('A2');
		foreach($result as $index=>$row){
			$taskName = isset($row['taskName'])?$row['taskName']:'';
			$stepStatus = '';
			if(isset($row['stepStatus'])){
				if($row['stepStatus'] == '1'){
					$stepStatus = '通过';
				}elseif($row['stepStatus'] == '-2'){
					$stepStatus = '不通过';
				}
			}
			$createTime = isset($row['createTime'])?$row['createTime']:'';
			$operateName = isset($row['operateName'])?$row['operateName']:'';
			$updateTime = isset($row['updateTime'])?$row['updateTime']:'';

			$id = isset($row['userinfo']['uid'])?$row['userinfo']['uid']:'';
			$approvalContent = '';
			if(isset($row['approvalContent'])){
				$approvalContent = json_decode($row['approvalContent'],true);
			}
			$excel->getActiveSheet()->setCellValue('A'.($index+2), $taskName);
			$excel->getActiveSheet()->setCellValue('B'.($index+2), $id);
			$excel->getActiveSheet()->setCellValue('C'.($index+2), $stepStatus);
			$excel->getActiveSheet()->setCellValue('D'.($index+2), $createTime);
			$excel->getActiveSheet()->setCellValue('E'.($index+2), $operateName."[".$updateTime."]");
			$leng = '';
			if(!empty($approvalContent)){
				foreach($approvalContent as $k=>$v){
					$leng .=  $k.":".$approvalContent[$k]."|";
				}
				$leng = substr($leng, 0, -1);
			}
			$excel->getActiveSheet()->setCellValue('F'.($index+2),$leng);
		}
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'. date('Y-m-d').'任务审核用户统计.xlsx"');
		header('Cache-Control: max-age=0');
		$writer = \PHPExcel_IOFactory::createWriter($excel,'Excel2007');
		$writer->save('php://output');
	}


	//$array 要排序的数组
	//$row  排序依据列
	//$type 排序类型[asc or desc]
	//return 排好序的数组
	private function array_sort($array,$row,$type){
		$array_temp = array();
		foreach($array as $v){
			$array_temp[$v[$row]] = $v;
		}
		if($type == 'asc'){
			ksort($array_temp);
		}elseif($type == 'desc'){
			krsort($array_temp);
		}else{
		}
		return $array_temp;
	}

	private function saveExcelToLocalFile($writer,$filename,$pageIndex=null){
		$filePath = '/downloads/'.$filename.'/';
		if(!is_dir(public_path() . $filePath)) {
			mkdir(public_path() . $filePath,0777,true);
		}
		if ($pageIndex) {
			$writer->save(public_path() . $filePath . $filename .'--'. $pageIndex.'.xlsx');
		} else {
			$writer->save(public_path() . $filePath . $filename .'.xlsx');
		}
		return $filePath;
	}

	private function addFileToZip($path,&$zip){
		$handler=opendir($path); //打开当前文件夹由$path指定。
		$i = 0;
		while(($filename=readdir($handler))!==false){
			if($filename != "." && $filename != ".."){//文件夹文件名字为'.'和‘..’，不要对他们进行操作
				if(is_dir($path."/".$filename)){// 如果读取的某个对象是文件夹，则递归
					self::addFileToZip($path."/".$filename, $zip);
				}else{ //将文件加入zip对象
					$zip->addFile($path."/".$filename,$filename);
					$i++;
				}
			}
		}
		@closedir($path);
	}

}