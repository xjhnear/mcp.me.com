<?php
namespace modules\phone\controllers;

use Youxiduo\Phone\Model\Category;
use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;

use Youxiduo\Phone\Model\PhoneBatch;
use Youxiduo\Phone\Model\PhoneNumbers;

use Illuminate\Support\Facades\DB;

class CategoryController extends BackendController
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
		$data['datalist'] = Category::getList($search,$pageIndex,$pageSize);
		$data['search'] = $search;
		$total = PhoneBatch::getCount($search);
		$pager = Paginator::make(array(),$total,$pageSize);
		$pager->appends($search);
		$data['pagelinks'] = $pager->links();
		$sql="SELECT category,sum(count) FROM m_phone_batch WHERE category <> '' GROUP BY category";
		$category_arr = DB::select($sql);
		$data['category_arr'] = $category_arr;
		return $this->display('category_list',$data);
	}

	public function getEdit($batch_id)
	{
		$data = array();
		$data['info'] = PhoneBatch::getInfo($batch_id);
		return $this->display('batch_info',$data);
	}

	public function postAjaxDownFile(){
		ini_set('max_execution_time', '0');
		ini_set("memory_limit", "1024M");
		$batch_id = Input::get('batch_id');
		$downType = Input::get('downType');
		$pageSize = Input::get('pageSize');
		$batch_code_down = Input::get('batch_code_down');
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
		$batch_code = Input::get('batch_code');
		$category = Input::get('category','');
		$ids = Input::get('ids');
		$bids = Input::get('bids');

		$input = array();
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
		foreach ($bids as $bid) {
			$search['batch_id'] = $bid;
			$i += PhoneNumbers::getCount($search);
			$sql="UPDATE m_phone_numbers SET batch_id = ".$re_batch." WHERE batch_id=".$bid;
			DB::update($sql);
			PhoneBatch::del($bid);
		}

		$data = array();
		$data['batch_id'] = $re_batch;
		$data['count'] = $i;
		$res = PhoneBatch::save($data);
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