<?php
namespace modules\a_giftbag\controllers;

use Yxd\Services\UserService;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;

use Youxiduo\Android\Model\Giftbag;
use Youxiduo\Android\Model\GiftbagAppoint;
use Youxiduo\Android\Model\GiftbagCard;
use Youxiduo\Android\GiftbagService;


class GiftCardController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'a_giftbag';
	}
	
	/**
	 * 礼包导入界面
	 */
	public function getImport($giftbag_id)
	{
		$data = array();
		$data['gift'] = Giftbag::m_getInfo($giftbag_id);
		return $this->display('giftcard-import',$data);
	}
	
	/**
	 * 礼包导入
	 */	
	public function postImport()
	{
		$giftbag_id = Input::get('giftbag_id');
		$type_repeat = (int)Input::get('type_repeat',0);
		$gift = Giftbag::m_getInfo($giftbag_id);
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
			$exists_card = GiftbagCard::m_getCardNoList($giftbag_id);
			$new_card = array_diff($card,$exists_card);
		}else{
			$new_card = $card;
		}
		if(!$new_card) return $this->back()->with('global_tips','礼包卡无效');
		$result = GiftbagCard::m_importCardNo($new_card, $giftbag_id);
		if($result){
			@unlink($target);
			return $this->redirect('a_giftbag/giftcard/list/' . $giftbag_id)->with('global_tips','礼包卡导入成功');
		}else{
			return $this->back()->with('global_tips','礼包卡导入失败');
		}
	}
	
    public function getInitQueue($giftbag_id)
	{
		 $giftbag_id && GiftbagService::initGiftbagCardNoQueue($giftbag_id);
		 return $this->back('礼包领取队列初始化成功');
	}
	
	public function getDelete($id)
	{
		GiftbagCard::m_delete($id);				
		return $this->back()->with('global_tips','礼包卡删除成功');
	}
	
	public function getClear($giftbag_id)
	{
		GiftbagCard::m_clear($giftbag_id);
		return $this->back()->with('global_tips','礼包卡清空成功');
	}
	
    public function getExportCard()
	{
		$giftbag_id = Input::get('giftbag_id');
		if(!$giftbag_id) return $this->back('礼包参数错误');
		$number = (int)Input::get('number');
		if($number<=0) return $this->back('卡号数量必须大于0');
		$result = GiftbagCard::exportCardNo($giftbag_id, $number);
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
	
	public function getList($giftbag_id=0)
	{
		$data = array();
		$giftbag_id = Input::get('giftbag_id',$giftbag_id);
		$search = array('giftbag_id'=>$giftbag_id);
		$is_get = Input::get('is_get',null);
		$search['startdate'] = Input::get('startdate');
		$search['enddate'] = Input::get('enddate');
		$search['uid'] = Input::get('uid');
		if($is_get!==null) $search['is_get'] = (int)$is_get;
		$page = Input::get('page',1);
		$pagesize = 10;
		$sort = $is_get ? array('gettime'=>'desc') : array('id'=>'desc');
		$result = GiftbagCard::m_search($search,$page,$pagesize,$sort);
		$uids = array();
		foreach($result['result'] as $row){
			if(!$row['uid']) continue;
			$uids[] = $row['uid'];
		}
		if($uids){
		    $users = UserService::getBatchUserInfo($uids,'full');
		    $data['users'] = $users; 
		}
		$pager = Paginator::make(array(),$result['total'],$pagesize);				
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $result['total'];
		$data['datalist'] = $result['result'];	
		return $this->display('giftcard-list',$data);
	}
}