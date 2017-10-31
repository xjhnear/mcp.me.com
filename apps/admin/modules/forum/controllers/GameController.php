<?php
namespace modules\forum\controllers;
use modules\forum\models\ForumModel;

use Yxd\Services\Cms\GameService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;

use Yxd\Modules\Core\BackendController;
use modules\forum\models\ChannelModel;

class GameController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'forum';
	}
	
	public function getOpen()
	{
		$game_id = Input::get('game_id');
		$result = ForumModel::doOpen($game_id);
		return $this->redirect('forum/channel/list?game_id=' . $game_id)->with('global_tips','论坛开启成功');
	}
	
	public function getClose()
	{
		$game_id = Input::get('game_id');
		$result = ForumModel::doClose($game_id);
		return $this->back()->with('global_tips','论坛关闭成功');
	}
	
	public function getSearch()
	{
		$page = Input::get('page',1);
		$search = array();
		$pagesize = 10;
		$result = ForumModel::search($search,$page,$pagesize);			
		$data['games'] = $result['result'];
		$data['imgurl'] = 'http://img.youxiduo.com';
		$data['gametype'] = array('0'=>'未分类')+(\Yxd\Services\Cms\GameService::getGameTypeOption());
		$data['pricetype'] = Config::get('yxd.game_pricetype');
		$data['zonetype'] = Config::get('yxd.game_zonetype');
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $result['total'];	
		return $this->display('forum-list',$data);
	}
	
	//public function get
}