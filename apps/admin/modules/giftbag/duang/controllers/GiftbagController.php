<?php
namespace modules\duang\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Youxiduo\Activity\Model\DuangGiftbag;
use Youxiduo\Activity\Model\DuangGiftbagCard;
use Yxd\Modules\Core\BackendController;

use Yxd\Services\UserService;

use Youxiduo\Activity\Share\ActivityService;
use Youxiduo\Activity\Share\GiftbagService;
use Youxiduo\Activity\Share\GoodsService;
use Youxiduo\Activity\Share\RechargeService;

use Youxiduo\V4\Game\Model\IosGame;

class GiftbagController extends BackendController
{
    public function _initialize()
	{
		$this->current_module = 'duang';
	}

	
	public function getImport($id)
	{
		$data = array();
        //print_r($id);exit;
        $giftbag = DuangGiftbag::getInfo($id);
		$data['giftbag'] = $giftbag;
		return $this->display('giftbag-import',$data);
	}
	
	public function postImport()
	{
		$giftbag_id = Input::get('giftbag_id');
		$type_repeat = (int)Input::get('type_repeat',0);
		$giftbag = DuangGiftbag::getInfo($giftbag_id);
		if(!$giftbag) return $this->back('礼包不存在');
	    if(!Input::hasFile('filedata')){
			return $this->back('礼包卡文件不存在');
		}
		$file = Input::file('filedata');
		$tmpfile = $file->getRealPath();
		$filename = $file->getClientOriginalName();
		$ext = $file->getClientOriginalExtension();				
		if(!in_array($ext,array('txt'))) return $this->back()->with('global_tips','上传文件格式错误');
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
		}
		
	    if(!$card || empty($card)) return $this->back('礼包卡无效');
		$new_card = array();
		$exists_card = array();
		
		if($type_repeat==1){
			$card = array_unique($card);
			$exists_card = DuangGiftbagCard::getCardNoList($giftbag_id);
			$new_card = array_diff($card,$exists_card);
		}else{
			$new_card = $card;
		}
		if(!$new_card) return $this->back('礼包卡无效');

		$groups = array_chunk($new_card,10);
		
		$data_cards = array();
		foreach($groups as $num=>$group){
			foreach($group as $cardno){
			    $data_cards[] = array('cardno'=>$cardno);
			}
		}
		$result = DuangGiftbagCard::importCardNoList($giftbag_id, $data_cards);
		if($result){
			@unlink($target);
			return $this->redirect('duang/giftbag/cardlist/' . $giftbag_id)->with('global_tips','礼包卡导入成功');
		}else{
			return $this->back('礼包卡导入失败');
		}
	}
	
	public function getDeleteCardno($id,$giftbag_id)
	{
		DuangGiftbagCard::deleteCardNo($id);
		DuangGiftbagCard::initCardNoNumber($giftbag_id);
		return $this->back('删除成功');
	}
	
	public function getCardlist($giftbag_id=0)
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
		$result = DuangGiftbagCard::searchCardNoList($search,$page,$pagesize,$sort);
		$uids = array();
		foreach($result['result'] as $row){
			if(!$row['user_id']) continue;
			$uids[] = $row['user_id'];
		}

		if($uids){
		    $users = UserService::getBatchUserInfo($uids,'full');
		    $data['users'] = $users; 
		}
		$pager = Paginator::make(array(),$result['totalCount'],$pagesize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $result['totalCount'];
		$data['datalist'] = $result['result'];	
		return $this->display('giftbag-cardlist',$data);
	}
}