<?php
namespace modules\v4_giftbag\controllers;

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
}