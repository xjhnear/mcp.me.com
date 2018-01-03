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
		$total = Category::getCount($search);
		$pager = Paginator::make(array(),$total,$pageSize);
		$pager->appends($search);
		$data['pagelinks'] = $pager->links();
		return $this->display('category_list',$data);
	}

	public function postAjaxDownFile(){
		ini_set('max_execution_time', '0');
		ini_set("memory_limit", "1024M");
		$category_id = Input::get('category_id');
		$downType = Input::get('downType');
		$pageSize = Input::get('pageSize');
		$batch_code_down = Input::get('batch_code_down');
		if(!$category_id) return json_encode(array('state'=>0,'msg'=>'数据异常'));
		$info_batch = PhoneBatch::getListByCategory($category_id);
		if(!$info_batch) return json_encode(array('state'=>0,'msg'=>'分类不存在'));
		$search = array();
		$pageIndex = 1;
		$pages = 1;
		$search['batch_id'] = $info_batch['batch_id'];
		$info_num_count = PhoneNumbers::getCount($search);
		if ($downType > 0) {
			if($batch_code_down) {
				$info_exists = PhoneBatch::getInfoByCode($batch_code_down);
				if ($info_exists) {
					return json_encode(array('state'=>0,'msg'=>'分类Code已存在'));
				} else {
					$input['batch_code'] = $batch_code_down;
					$input['count'] = ($info_num_count>=$pageSize)?$pageSize:$info_num_count;
				}
			} else {
				return json_encode(array('state'=>0,'msg'=>'分类Code不能为空'));
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
			return json_encode(array("state"=>1,'msg'=>'分类合并成功'));
		}else{
			return json_encode(array('state'=>0,'msg'=>'分类合并失败'));
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

}