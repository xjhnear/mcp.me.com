<?php
namespace modules\adv\controllers;

use modules\adv\models\GameCreditModel;

use Yxd\Services\Cms\GameService;
use Yxd\Modules\Core\CacheService;
use Illuminate\Support\Facades\Input;
use Yxd\Modules\Core\BackendController;

class CreditController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'adv';
	}
	
	public function getIndex()
	{
		$data = array();
		$page = Input::get('page',1);
		$data['datalist'] = GameCreditModel::search(array(),$page);
		$data['imgurl'] = 'http://img.youxiduo.com';
		return $this->display('game-credit-list',$data);
	}
	
	public function getAdd($game_id=0)
	{
		$game = $game_id ? array() :GameService::getGameInfo($game_id);
		$data['credit'] = array('game_id'=>$game_id);
		$data['game'] = $game;
		return $this->display('game-credit-edit',$data);
	}
	
	public function getEdit($id)
	{
		$info = GameCreditModel::getInfo($id);
		$data['credit'] = $info;
		$data['game'] = $info['game'];
		return $this->display('game-credit-edit',$data);
	}
	
	public function postSave()
	{
		$input = Input::only('id','game_id','score');
		GameCreditModel::save($input);
		CacheService::section('adv::gamecredit')->flush();
		$this->operationPdoLog('修改游戏下载游币奖励', $input);
		return $this->redirect('adv/credit/index')->with('global_tips','保存成功');
	}
	
	public function getDelete($id)
	{
		GameCreditModel::delete($id);
		CacheService::section('adv::gamecredit')->flush();
		return $this->back()->with('global_tips','删除成功');
	}
}