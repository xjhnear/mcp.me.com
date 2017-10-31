<?php
namespace modules\game\controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Yxd\Modules\Core\BackendController;

use Youxiduo\V4\Game\Model\UserGame;
use Youxiduo\V4\Game\Model\UserGameArea;
use Youxiduo\V4\Game\Model\GameArea;

class AreaController extends BackendController
{
	protected $arealist = array('1'=>'IOS正版','2'=>'越狱(itools)','3'=>'越狱（同步推）','4'=>'越狱（91助手）','5'=>'越狱（PP助手）');
	public function _initialize()
	{
		$this->current_module = 'game';
	}
	
	public function getList()
	{
		$search = Input::only('game_id');
		$data = array();
		$result = GameArea::searchList($search);
		$total  = GameArea::searchCount($search);
		$data['datalist'] = $result;
		return $this->display('game-area-list',$data);
	}
	
	public function getEdit($id=0)
	{
		$data = array();
		$data['arealist'] = $this->arealist;
		if($id){
		    $data['area'] = GameArea::getInfo($id);
		}
		return $this->display('game-area-edit',$data);
	}
	
	public function postEdit()
	{
		$input = Input::only('id','game_id','type','area_name');
		$input['typename'] = $this->arealist[$input['type']];
		$input['ctime'] = time();
		$input['platform'] = 'ios';
		$input['uid'] = 0;
		
		$success = GameArea::save($input);
		return $this->redirect('game/area/list','保存成功');
	}
}