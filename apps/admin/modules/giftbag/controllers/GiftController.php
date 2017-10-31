<?php
namespace modules\giftbag\controllers;

use modules\giftbag\models\GiftCardModel;

use Yxd\Modules\Message\PromptService;

use Yxd\Modules\Activity\GiftbagService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Paginator;
use Youxiduo\System\AuthService;
use Yxd\Modules\Core\BackendController;
use modules\giftbag\models\GiftbagModel;
use Doctrine\Tests\Common\Annotations\Null;
use Illuminate\Support\Facades\Response;
use Yxd\Services\UserService;
use Yxd\Services\Cms\GameService;

use PHPImageWorkshop\ImageWorkshop;

class GiftController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'giftbag';
	}
	
	/**
	 * 
	 */
	public function getSearch()
	{
		$data = array();
		$page = Input::get('page',1);
		$pagesize = 10;
		$search = Input::only('keyword','game_id');
		$result = GiftbagModel::search($search,$page,$pagesize);
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $result['total'];
		$data['datalist'] = $result['result'];	
		$data['allow_add'] = AuthService::verifyNodeAuth('giftbag/gift/add');
		$data['allow_edit'] = AuthService::verifyNodeAuth('giftbag/gift/edit');
		$data['allow_push'] = AuthService::verifyNodeAuth('giftbag/gift/push');
		$data['allow_appoint'] = AuthService::verifyNodeAuth('giftbag/gift/ajax-appoint-uids');
		$data['allow_import'] = AuthService::verifyNodeAuth('giftbag/giftcard/import');
		$data['allow_list_card'] = AuthService::verifyNodeAuth('giftbag/giftcard/list');
		
		
		return $this->display('gift-search',$data);
	}

	/**
	 * 添加礼包
	 */
	public function getAdd()
	{
		$data = array();
		$data['gift'] = array('is_show'=>1);
		return $this->display('gift-info',$data);
	}
	
    /**
	 * 编辑礼包
	 */
	public function getEdit($id)
	{
		$data = array();
		$data['gift'] = GiftbagModel::getInfo($id);
		//$this->addWaterMark($data['gift']['game_id']);
		return $this->display('gift-info',$data);
	}
	
	/**
	 * 保存礼包信息
	 */
	public function postSave()
	{
		$input = array();
		$input['id'] = Input::get('id');
		$input['title'] = Input::get('title');
		$input['shorttitle'] = Input::get('shorttitle','');
		$input['is_ios'] = 1;//Input::get('is_ios',1);
		//$input['is_android'] = Input::get('is_android',0);
		$input['game_id'] = (int)Input::get('game_id');
		//$input['listpic'] = Input::get('listpic','');
		$input['starttime'] = strtotime(Input::get('starttime'));
		$input['endtime'] = strtotime(Input::get('endtime'));
		$input['sort'] = (int)Input::get('sort',50);
		$input['is_hot'] = (int)Input::get('is_hot',0);
		$input['is_top'] = (int)Input::get('is_top',0);
		$input['is_show'] = (int)Input::get('is_show',0);
		$input['is_activity'] = (int)Input::get('is_activity',0);
		$input['is_appoint'] = (int)Input::get('is_appoint',0);
		$input['is_charge'] = (int)Input::get('is_charge',0);
		$input['is_not_limit'] = (int)Input::get('is_not_limit',0);
		$input['mutex_giftbag_id'] = (int)Input::get('mutex_giftbag_id',0);
		$input['limit_register_time'] = Input::get('limit_register_time');
		$input['content'] = Input::get('content');
		$condition = array('score'=>Input::get('score',0));
		$input['condition'] = json_encode($condition);
		$input['limit_count'] = Input::get('limitcount');
		//$input['ctime'] = time();
		
	    if($input['is_appoint']){
			$input['appoint_icon'] = $this->addWaterMark($input['game_id'],false,true);
		}
		
		if($input['is_charge']){
			$input['charge_icon'] = $this->addWaterMark($input['game_id'],true);
		}

		if($input['limit_register_time']){
			$input['limit_register_time'] = strtotime($input['limit_register_time']);
		}else{
			$input['limit_register_time'] = 0;
		}
				
		
		$id = GiftbagModel::save($input);
		if($id){
			return $this->redirect('giftbag/gift/search')->with('global_tips','礼包保存成功');
		}
	}
	
	protected function addWaterMark($game_id,$is_charge=false,$is_appoint=false)
	{
		$game = GameService::getGameInfo($game_id);
		if($game){
			$icon = $game['ico'];
			$rootPath = storage_path();
			$oldPath = Config::get('app.game_icon_path');
			
			//$oldPath = $rootPath;
			//$icon = '/userdirs/2015/02/a.jpg';
			$file = $oldPath . $icon;
			$savePath = $oldPath;
			
			$dirPath = pathinfo($icon,PATHINFO_DIRNAME);
			$filename = pathinfo($icon,PATHINFO_BASENAME);			
			$newfile = str_replace('.','_'.Str::random(4).'.',$filename);
			$saveFile = $dirPath . DIRECTORY_SEPARATOR . $newfile;
			$layer = ImageWorkshop::initFromPath($file);
			$layer->resizeInPixel(100, null, true); 
			$watermake = 'image';
			if($watermake=='text'){
				//文本水印
				$text = '付费礼包';
				$fontSize = 12;
				$fontPath = base_path() . '/config/simsun.ttc';
				$color = 'FFFFFF';
				$postionX = 0;
				$postionY = 0;
				$backgroundColor = 'FF0000';
				$textLayer = ImageWorkshop::initTextLayer($text,$fontPath,$fontSize,$color,0,$backgroundColor);
				$layer->addLayerOnTop($textLayer, 0, 0, "LT");
			}else{
				//图片水印
				$water_icon = $is_charge ? 'price.png' : 'appoint.png';
				$watermakeLayer = ImageWorkshop::initFromPath(base_path() . '/config/'.$water_icon);
				$layer->addLayer(1,$watermakeLayer,0,0,'LT');
			}
			$layer->save($savePath,$saveFile,true);
			//$image = $layer->getResult();
			//header('Content-type: image/jpeg');
            //imagejpeg($image, null, 95);
            return $saveFile;            
		}
		return '';
	}
	
	public function getPush($id)
	{
		$data = array('giftbag_id'=>$id);
		$hot = PromptService::pushHotGiftToQueue($data);
		$reserve = PromptService::pushReserveToQueue($data);
		if($reserve){
			$update = array('id'=>$id,'is_send'=>1);
			GiftbagModel::save($update);
			return $this->redirect('giftbag/gift/search')->with('global_tips','推送成功');
		}else{
			return $this->redirect('giftbag/gift/search')->with('global_tips','推送失败');
		}		
		
	}
	
	/**
	 * 获取专属礼包所指定的用户
	 * @return Ambigous <NULL, string>
	 */
	public function getAjaxAppointUids(){
		$giftbag_id = Input::get('giftbag_id');
		$uids_str = Null;
		if(is_numeric($giftbag_id) && $giftbag_id > 0){
			$uids = GiftbagModel::getGiftbagAppointUids($giftbag_id);
			$uids_str = implode(',', $uids);
		}
		return $uids_str;
	}
	
	public function postAjaxAppointUids(){
		$return_data =array();
		$format_error = false;
		$giftbag_id = Input::get('giftbag_id');
		$uids_str = Input::get('uids_str');
		$filter_device = strval(Input::get('filter_device'));
		$uids_arr = explode(',', $uids_str);
		$uids_arr = array_filter($uids_arr);
		$uids_arr = array_unique($uids_arr);
		if($uids_arr){
			foreach ($uids_arr as $row){
				if(!is_numeric($row)) $format_error = true;
			}
		}
		if(empty($giftbag_id)){
			$return_data['status'] = 0;
			$return_data['msg'] = '数据错误，请刷新页面重试！';
			return Response::json($return_data);
		}elseif($format_error) {
			$return_data['status'] = 0;
			$return_data['msg'] = '数据格式错误，请重新填写！';
			return Response::json($return_data);
		}elseif(empty($uids_arr)) {
			$return_data['status'] = 0;
			$return_data['msg'] = '用户ID不能为空，请重新填写！';
			return Response::json($return_data);
		}else{
			$add_data = array();
			
			if($filter_device=='true'){
			    //$exists_uids = GiftbagModel::getGiftbagAppointUids($giftbag_id);
			    //$exists_users = UserService::getAppleIdentifyByUids($exists_uids);
			    //$new_uids = array_diff($uids_arr,$exists_uids);
			    $new_uids = $uids_arr;
			    $new_users = UserService::getAppleIdentifyByUids($new_uids);
			    //$exists_devices = array_flip($exists_users);
			    $new_devices = array_flip($new_users);
			    //$real_devices = $exists_devices + $new_devices;
			    $real_devices = $new_devices;
			    $real_uids = array_values($real_devices);
			    //return Response::json($real_uids);
			    foreach($real_uids as $uid){
			    	$add_data[] = array(
						'giftbag_id' => $giftbag_id,
						'uid' => $uid,
						'add_time' => time()
					);
			    }
			}else{
				foreach ($uids_arr as $uid){
					$add_data[] = array(
						'giftbag_id' => $giftbag_id,
						'uid' => $uid,
						'add_time' => time()
					);
				}
			}
			//return Response::json($add_data);
			
			if($add_data && GiftbagModel::updateGiftbagAppointUids($giftbag_id,$add_data)){
				$return_data['status'] = 0;
				$return_data['msg'] = '保存成功！';
				return Response::json($return_data);
			}else{
				$return_data['status'] = 0;
				$return_data['msg'] = '保存失败！请稍后重试！';
				return Response::json($return_data);
			}
		}
	}
	
	public function getReport()
	{
		$startdate = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$enddate = mktime(23,59,59,date('m'),date('d'),date('Y'));		
		$pageIndex = (int)Input::get('page',1);
		$s = Input::get('startdate','');
		$d = Input::get('enddate','');
		!empty($s) && $startdate = strtotime($s);
		!empty($d) && $enddate = strtotime($d)+60*60*24-1;
		
		$search = array('startdate'=>date('Y-m-d',$startdate),'enddate'=>date('Y-m-d',$enddate));
		
		$pageSize=15;
		$data = array();
		$result = GiftCardModel::getReport($startdate, $enddate,$pageIndex,$pageSize);
		$pager = Paginator::make(array(),$result['total'],$pageSize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['datalist'] = $result['result'];
		$data['total_count'] = $result['total_count'];
		return $this->display('gift-report',$data);
	}
}