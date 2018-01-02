<?php
namespace modules\phone\controllers;

use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;

use Youxiduo\Phone\Model\PhoneBatch;
use Youxiduo\Phone\Model\PhoneNumbers;
use Youxiduo\Phone\Model\Category;

use Illuminate\Support\Facades\DB;

class BatchController extends BackendController
{
	public function _initialize(){
		$this->current_module = 'phone';
	}
	
	public function getList()
	{
		$pageIndex = Input::get('page',1);
		$search = Input::only('batch_code','category');
		$pageSize = 10;
		$data = array();
		$data['datalist'] = PhoneBatch::getList($search,$pageIndex,$pageSize);
		$data['search'] = $search;
		$total = PhoneBatch::getCount($search);
		$pager = Paginator::make(array(),$total,$pageSize);
		$pager->appends($search);
		$data['pagelinks'] = $pager->links();
		$category_arr = Category::getListAllName();
		$data['category_arr'] = $category_arr;
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
		$category_arr = Category::getListAllName();
		$data['category_arr'] = $category_arr;
		$data['info'] = PhoneBatch::getInfo($batch_id);
		return $this->display('batch_info',$data);
	}
	
	public function postSave()
	{
		$input = Input::only('batch_id','batch_code','coefficient','category');
		$input['batch_code'] = 'B'.time();
		$result = PhoneBatch::save($input);
		if($result){
			return $this->redirect('phone/batch/list','批次保存成功');
		}else{
			return $this->back('批次保存成功');
		}
	}

	public function postAjaxDel()
	{
		$batch_id = Input::get('batch_id');
		if($batch_id){
			$sql="DELETE FROM m_phone_numbers WHERE batch_id=".$batch_id;
			DB::delete($sql);
			PhoneBatch::del($batch_id);
		}
		return json_encode(array('state'=>1,'msg'=>'批次删除成功'));
	}

	public function postAjaxUploadFile(){
		set_time_limit(0);
		ini_set("memory_limit", "1024M");
		ini_set("post_max_size", "100M");
		ini_set("upload_max_filesize", "100M");
		setlocale(LC_ALL, 'zh_CN');
		$batch_code = 'B'.time();
		$category = Input::get('category');
		if(!Input::hasFile('append_file'))
			return json_encode(array('state'=>0,'msg'=>'文件不存在'));
		$file = Input::file('append_file');
		$tmpfile = $file->getRealPath();
		$filename = $file->getClientOriginalName();
		$ext = $file->getClientOriginalExtension();
		if(!in_array($ext,array('csv','txt'))) return json_encode(array('state'=>0,'msg'=>'上传文件格式错误'));
		$server_path = storage_path() . '/tmp/';
		$newfilename = microtime() . '.' . $ext;
		$target = $server_path . $newfilename;
		$file->move($server_path,$newfilename);
		$input = array();
		$i_c = $unicom_c = $mobile_c = $telecom_c = 0;
		if($category) {
			$category_exists = Category::getInfoByName($category);
			if ($category_exists) {
				$category = $category_exists['category_id'];
				$unicom_c = $category_exists['unicom'];
				$mobile_c = $category_exists['mobile'];
				$telecom_c = $category_exists['telecom'];
				$i_c = $category_exists['count'];
			} else {
				$input['name'] = $category;
				$re_category = Category::save($input);
				$category = $re_category;
			}
		}
		if($batch_code) {
			$info_exists = PhoneBatch::getInfoByCode($batch_code);
			if ($info_exists) {
				return json_encode(array('state'=>0,'msg'=>'批次Code已存在'));
			} else {
				$input['batch_code'] = $batch_code;
				$input['category'] = $category;
			}
		} else {
			return json_encode(array('state'=>0,'msg'=>'批次Code不能为空'));
		}
		$re_batch = PhoneBatch::save($input);


		$handle = fopen($target, 'r');
		$result = self::input_csv($handle); //解析csv
		$len_result = count($result);
		if($len_result==0){
			return json_encode(array('state'=>0,'msg'=>'没有任何数据'));
		}
		$i = 0;
		$j = 0;
		$unicom = $mobile = $telecom = 0;
		$sql="INSERT IGNORE INTO m_phone_numbers (batch_id,phone_number,operator,city,address) VALUES";

		for ($j = 1; $j < $len_result; $j++) { //循环获取各字段值
			$phone_number = isset($result[$j][0])?self::characet($result[$j][0]):''; //中文转码
			$operator = isset($result[$j][1])?self::characet($result[$j][1]):'';
			switch ($operator) {
				case "联通":
					$unicom++;
					break;
				case "移动":
					$mobile++;
					break;
				case "电信":
					$telecom++;
					break;
			}
			$city = isset($result[$j][2])?self::characet($result[$j][2]):'';
			$address = isset($result[$j][3])?self::characet($result[$j][3]):'';
			if ($phone_number==''&&$operator==''&&$city==''&&$address=='') continue;
			$tmpstr = "'". $re_batch ."','". $phone_number ."','". $operator ."','". $city ."','". $address ."'";
			$sql .= "(".$tmpstr."),";
			$j++;
		}
		fclose($handle); //关闭指针
		$sql = substr($sql,0,-1);   //去除最后的逗号
		DB::insert($sql);

		$search['batch_id'] = $re_batch;
		$info_num_count = PhoneNumbers::getCount($search);
		$i = $info_num_count;

		if ($i == 0) {
			PhoneBatch::del($re_batch);
			return json_encode(array('state'=>0,'msg'=>'批次添加失败,所有导入数据均已存在'));
		}
		$data = array();
		$data['batch_id'] = $re_batch;
		$data['unicom'] = $unicom;
		$data['mobile'] = $mobile;
		$data['telecom'] = $telecom;
		$data['count'] = $i;
		$res = PhoneBatch::save($data);
		$data_c = array();
		$data_c['category_id'] = $category;
		$data_c['count'] = $i_c + $i;
		$data_c['unicom'] = $unicom_c + $unicom;
		$data_c['mobile'] = $mobile_c + $mobile;
		$data_c['telecom'] = $telecom_c + $telecom;
		$res_c = Category::save($data_c);

		if($res){
			return json_encode(array("state"=>1,'msg'=>'批次添加成功,文件读取数据'.$j.'条,共实际导入数据'.$i.'条'));
		}else{
			return json_encode(array('state'=>0,'msg'=>'批次添加失败'));
		}
	}

	public function postAjaxDownFile(){
		ini_set('max_execution_time', '0');
		ini_set("memory_limit", "1024M");
		$batch_id = Input::get('batch_id');
		$downType = Input::get('downType');
		$pageSize = Input::get('pageSize');
		$batch_code_down = 'B'.time();
		if(!$batch_id) return json_encode(array('state'=>0,'msg'=>'数据异常'));
		$info_batch = PhoneBatch::getInfo($batch_id);
		if(!$info_batch) return json_encode(array('state'=>0,'msg'=>'批次不存在'));
		$search = array();
		$pageIndex = 1;
		$pages = 1;
		$search['batch_id'] = $info_batch['batch_id'];
		$info_num_count = PhoneNumbers::getCount($search);
		if ($downType > 0) {
			if($batch_code_down) {
				$info_exists = PhoneBatch::getInfoByCode($batch_code_down);
				if ($info_exists) {
					return json_encode(array('state'=>0,'msg'=>'批次Code已存在'));
				} else {
					$input['batch_code'] = $batch_code_down;
					$input['count'] = ($info_num_count>=$pageSize)?$pageSize:$info_num_count;
				}
			} else {
				return json_encode(array('state'=>0,'msg'=>'批次Code不能为空'));
			}
			$re_batch = PhoneBatch::save($input);

			$sql="UPDATE m_phone_numbers SET batch_id = ".$re_batch." WHERE batch_id=".$batch_id." ORDER BY num_id DESC LIMIT ".$pageSize;
			DB::update($sql);
			if ($info_num_count>$pageSize) {
				$data = array();
				$data['batch_id'] = $batch_id;
				$data['count'] = $info_num_count-$pageSize;
				$res = PhoneBatch::save($data);
			} else {
				PhoneBatch::del($batch_id);
			}
			$batch_id = $re_batch;
			$search['batch_id'] = $re_batch;
			$batch_code = $batch_code_down;

		} else {
			$pageSize = $info_num_count;
			$search['batch_id'] = $info_batch['batch_id'];
			$batch_code = $info_batch['batch_code'];
		}

//		if ($pageSize > 0) {
//			$info_num_count = PhoneNumbers::getCount($search);
//			$pages = ceil($info_num_count/$pageSize);
//		} else {
//			$pages = 1;
//		}

		while($pageIndex<=$pages) {
			$info_num = PhoneNumbers::getList($search,$pageIndex,$pageSize);
			if ($info_num) {
				$str = "手机号码,运营商,城市,地址\n";
				$str = iconv('utf-8','gb2312',$str);
				foreach($info_num as $index=>$row){
					$phone_number = iconv('utf-8','gb2312',$row['phone_number']); //中文转码
					$operator = iconv('utf-8','gb2312',$row['operator']); //中文转码
					$city = iconv('utf-8','gb2312',$row['city']); //中文转码
					$address = iconv('utf-8','gb2312',$row['address']);
					$str .= $phone_number.",".$operator.",".$city.",".$address."\n"; //用引文逗号分开
				}
				$filename = $batch_code .'--'. date('Ymd'); //设置文件名
				self::saveExcelToLocalFile($str,$filename,$pageIndex); //导出
			}
			$pageIndex++;
		}

		$zipname = $batch_code .'--'. date('Ymd');
		$zip = new \ZipArchive();
		if($zip->open(public_path().'/downloads/'.$zipname.'.zip', \ZipArchive::CREATE) === TRUE) {
			self::addFileToZip(public_path().'/downloads/'.$filename, $zip); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
			$zip->close(); //关闭处理的zip文件
		}

		$data_batch = array();
		$data_batch['batch_id'] = $batch_id;
		$data_batch['down_at'] = time();
		$data_batch['is_new'] = 0;
		PhoneBatch::save($data_batch);

		$url = '/downloads/'.$zipname.'.zip';
		return json_encode(array('state'=>1,'url'=>$url));
	}

	public function postAjaxMerge(){
		set_time_limit(0);
		ini_set("memory_limit", "1024M");
		$batch_code = 'B'.time();
		$category = Input::get('category','');
		$ids = Input::get('ids');
		$bids = Input::get('bids');

		$input = array();
		$i_c = $unicom_c = $mobile_c = $telecom_c = 0;
		if($category) {
			$category_exists = Category::getInfoByName($category);
			if ($category_exists) {
				$category = $category_exists['category_id'];
				$unicom_c = $category_exists['unicom'];
				$mobile_c = $category_exists['mobile'];
				$telecom_c = $category_exists['telecom'];
				$i_c = $category_exists['count'];
			} else {
				$input['name'] = $category;
				$re_category = Category::save($input);
				$category = $re_category;
			}
		}
		if($batch_code) {
			$info_exists = PhoneBatch::getInfoByCode($batch_code);
			if ($info_exists) {
				return json_encode(array('state'=>0,'msg'=>'批次Code已存在'));
			} else {
				$input['batch_code'] = $batch_code;
				$input['category'] = $category;
			}
		} else {
			return json_encode(array('state'=>0,'msg'=>'批次Code不能为空'));
		}
		$re_batch = PhoneBatch::save($input);
		$i = 0;
		$unicom = $mobile = $telecom = 0;
		foreach ($bids as $bid) {
			$data_info = PhoneBatch::getInfo($bid);
			$i += $data_info['count'];
			$unicom += $data_info['unicom'];
			$mobile += $data_info['mobile'];
			$telecom += $data_info['telecom'];
			$data_category = Category::getInfo($data_info['category']);
			$update_arr = array();
			$update_arr['category_id'] = $data_category['category_id'];
			$update_arr['count'] = $data_category['count'] - $data_info['count'];
			$update_arr['unicom'] = $data_category['unicom'] - $data_info['unicom'];
			$update_arr['mobile'] = $data_category['mobile'] - $data_info['mobile'];
			$update_arr['telecom'] = $data_category['telecom'] - $data_info['telecom'];
			Category::save($update_arr);
			$sql="UPDATE m_phone_numbers SET batch_id = ".$re_batch." WHERE batch_id=".$bid;
			DB::update($sql);
			PhoneBatch::del($bid);
		}

		$data = array();
		$data['batch_id'] = $re_batch;
		$data['unicom'] = $unicom;
		$data['mobile'] = $mobile;
		$data['telecom'] = $telecom;
		$data['count'] = $i;
		$res = PhoneBatch::save($data);
		$data_c = array();
		$data_c['category_id'] = $category;
		$data_c['count'] = $i_c + $i;
		$data_c['unicom'] = $unicom_c + $unicom;
		$data_c['mobile'] = $mobile_c + $mobile;
		$data_c['telecom'] = $telecom_c + $telecom;
		$res_c = Category::save($data_c);

		if($res){
			return json_encode(array("state"=>1,'msg'=>'批次合并成功'));
		}else{
			return json_encode(array('state'=>0,'msg'=>'批次合并失败'));
		}
	}

	private function saveExcelToLocalFile($data,$filename,$pageIndex=null){
		$filePath = '/downloads/'.$filename.'/';
		if(!is_dir(public_path() . $filePath)) {
			mkdir(public_path() . $filePath,0777,true);
		}

		if ($pageIndex) {
			$fp = fopen(public_path() . $filePath . $filename .'--'. $pageIndex.'.csv','a');
		} else {
			$fp = fopen(public_path() . $filePath . $filename .'.csv','a');
		}
		fwrite($fp, $data);
		fclose($fp);
		return $filePath;
	}

	private function saveCsvToLocalFile($writer,$filename,$pageIndex=null){
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

	private function input_csv($handle) {
		$out = array ();
		$n = 0;
		while ($data = fgetcsv($handle, 10000)) {
			$num = count($data);
			if ($num == 1) {
				//$data[0] = trim($data[0], "\xEF\xBB\xBF");
				if (strpos($data[0],"\t") > 0) {
					$data[0] = preg_replace("/\t/",",",$data[0]);
					$data = explode(',',$data[0]);
					$num = count($data);
				}
			}
			for ($i = 0; $i < $num; $i++) {
				$out[$n][$i] = $data[$i];
			}
			$n++;
		}
		return $out;
	}

	private function characet($data){
		if( !empty($data) ){
			$fileType = mb_detect_encoding($data , array('UTF-8','GBK','LATIN1','BIG5')) ;
			if( $fileType != 'UTF-8'){
				 $data = mb_convert_encoding($data ,'utf-8' , $fileType.'//IGNORE');
			}
		}
		return $data;
	}

	private function export_csv($filename,$data) {
		header("Content-type:text/csv");
		header("Content-Disposition:attachment;filename=".$filename);
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
		header('Expires:0');
		header('Pragma:public');
		echo $data;exit;
	}

	public function postAjaxUploadFileExcel(){
		set_time_limit(0);
		ini_set("memory_limit", "1024M");
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

		require_once base_path() . '/libraries/SpreadsheetReader/php-excel-reader/excel_reader2.php';
		require_once base_path() . '/libraries/SpreadsheetReader/SpreadsheetReader.php';
		$Reader = new \SpreadsheetReader($target);
		$Sheets = $Reader -> Sheets();
		$i = 0;
		$sql="INSERT INTO m_phone_numbers (batch_id,phone_number,operator,city,address) VALUES";
		foreach ($Sheets as $Index => $Name)
		{
			$Reader -> ChangeSheet($Index);
			$j = 0;
			foreach ($Reader as $Row)
			{
				if ($j > 0) {
					$tmpstr = "'". $re_batch ."','". $Row[0] ."','". $Row[1] ."','". $Row[2] ."','". $Row[3] ."'";
					$sql .= "(".$tmpstr."),";
//					$data_tmp = array();
//					$data_tmp['batch_id'] = $re_batch;
//					$data_tmp['phone_number'] = isset($Row[0])?$Row[0]:'';
//					$data_tmp['operator'] = isset($Row[1])?$Row[1]:'';
//					$data_tmp['city'] = isset($Row[2])?$Row[2]:'';
//					$data_tmp['address'] = isset($Row[3])?$Row[3]:'';
//					$re_num = PhoneNumbers::save($data_tmp);
					$i++;
				}
				$j++;
			}
		}
		$sql = substr($sql,0,-1);   //去除最后的逗号
		DB::insert($sql);

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

	public function postAjaxDownFileExcrl(){
		ini_set('max_execution_time', '0');
		ini_set("memory_limit", "1024M");
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
				$excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
				$excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
				$excel->getActiveSheet()->getColumnDimension('D')->setWidth(60);
				$excel->getActiveSheet()->setCellValue('A1','手机号码');
				$excel->getActiveSheet()->setCellValue('B1','运营商');
				$excel->getActiveSheet()->setCellValue('C1','城市');
				$excel->getActiveSheet()->setCellValue('D1','地址');
				$excel->getActiveSheet()->freezePane('A2');
				foreach($info_num as $index=>$row){
					$phone_number = isset($row['phone_number'])?$row['phone_number']:'';
					$operator = isset($row['operator'])?$row['operator']:'';
					$city = isset($row['city'])?$row['city']:'';
					$address = isset($row['address'])?$row['address']:'';

					$excel->getActiveSheet()->setCellValue('A'.($index+2), $phone_number);
					$excel->getActiveSheet()->setCellValue('B'.($index+2), $operator);
					$excel->getActiveSheet()->setCellValue('C'.($index+2), $city);
					$excel->getActiveSheet()->setCellValue('D'.($index+2), $address);

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

		$data_batch = array();
		$data_batch['batch_id'] = $batch_id;
		$data_batch['down_at'] = time();
		$data_batch['is_new'] = 0;
		PhoneBatch::save($data_batch);

		$url = '/downloads/'.$zipname.'.zip';
		return json_encode(array('state'=>1,'url'=>$url));
	}


}