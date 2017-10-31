<?php
namespace modules\wxshare\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\Core\BackendController;

use Youxiduo\Activity\Share\ActivityService;
use Youxiduo\Activity\Share\GiftbagService;
use Youxiduo\Activity\Share\GoodsService;
use Youxiduo\Activity\Share\RechargeService;

use Yxd\Services\UserService;
use Youxiduo\V4\Game\Model\IosGame;

class RechargeController extends BackendController
{
    public function _initialize()
	{
		$this->current_module = 'wxshare';
	}
	
    public function getList()
	{
		$search = array();
		$data = array();
		$pageIndex = Input::get('page',1);
		$pageSize = 10;
		$result = RechargeService::searchList($search,$pageIndex,$pageSize);
		$data['datalist'] = $result['result'];
		
		return $this->display('recharge-list',$data);
	}
	
	public function getEdit($id=0)
	{
		$data = array();
		if($id){
			$data['recharge'] = RechargeService::getInfo($id);
		}
		return $this->display('recharge-info',$data);
	}
	
    public function postEdit()
	{
		$input['id'] = Input::get('id');
		$input['title'] = Input::get('title');
		$input['game_id'] = (int)Input::get('game_id');
		$input['sort'] = (int)Input::get('sort',50);
		$input['is_show'] = (int)Input::get('is_show',0);
		
		$game = IosGame::getInfoById($input['game_id']);
		if(!$game) return $this->back('没有选择任何游戏');
		$input['game_name'] = $game['shortgname'];
		$input['ico'] = $game['ico'];
		
	    $id = RechargeService::saveInfo($input);
	    if($id){
			return $this->redirect('wxshare/recharge/list')->with('global_tips','产品保存成功');
		}
	}
	
	public function getPricelist($recharge_id)
	{
		$data = array();
		$data['proxy_id'] = $recharge_id;
		$result = RechargeService::searchPriceList(array('proxy_id'=>$recharge_id),1,20);
		$data['datalist'] = $result['result'];
		return $this->display('recharge-price-list',$data);
	}
	
	public function getDeletePrice($id)
	{
		RechargeService::deletePrice($id);
		return $this->back('删除成功');
	}
	
    public function getEditPrice($id=0,$proxy_id)
	{
		$data = array();
		
		if($id){
			$data['price'] = RechargeService::getPriceInfo($id);
		}
		$data['proxy_id'] = $proxy_id;
		return $this->display('recharge-price-info',$data);
	}
	
    public function postEditPrice()
	{
		$input['id'] = Input::get('id');
		$input['title'] = Input::get('title');
		$input['proxy_id'] = (int)Input::get('proxy_id');
		$input['price'] = (int)Input::get('price',0);
		$input['is_show'] = (int)Input::get('is_show',0);
		
	    $id = RechargeService::savePriceInfo($input);
	    if($id){
			return $this->redirect('wxshare/recharge/pricelist/'.$input['proxy_id'])->with('global_tips','产品保存成功');
		}
	}
}