<?php
namespace modules\game\controllers;
use Yxd\Modules\Core\BackendController;
use Yxd\Services\Cms\GameService;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use modules\game\models\GameModel;

class ApiController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'game';
	}
	
	public function getGameSelectSearch()
	{	
		$out = array();
		$keyword = Input::get('q');
		$search = array();
		if($keyword){
			$search['gname'] = $keyword;
		}
		$result = GameModel::search($search,1,20);
		foreach($result['result'] as $row){
			$out['game_list'][] = array(
		    'id'=>$row['id'],
		    'text'=>$row['shortgname']
		);
		}	
		
		return $this->json($out);
		//exit(json_encode($out));
	}
	
    public function getGameSelectInit()
	{
		$id = Input::get('id');
		if($id){
			$game = GameModel::getInfo($id);
			if($game){
				return $this->json(array('id'=>$game['id'],'text'=>$game['shortgname']));
			}
		}
	}
	
    public function getGameSearch()
	{
		$keytype = Input::get('keytype','id');
		$keyword = Input::get('keyword');
		$search = array();
		if($keytype=='id'){
			$search['id'] = $keyword;
		}else{
			$search['gname'] = $keyword;
		}
		
		$page = Input::get('page',1);
		$pagesize = 6;
		$data = array();	
		$data['keytype'] = $keytype;
		$data['keyword'] = $keyword;
		$data['gametype'] = array('0'=>'未分类')+(GameService::getGameTypeOption());
		$data['pricetype'] = Config::get('yxd.game_pricetype');
		$data['zonetype'] = Config::get('yxd.game_zonetype');
		$data['imgurl'] = Config::get('app.img_url');
		$result = GameModel::search($search,$page,$pagesize);	
		$data['games'] = $result['result'];
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$pager->appends(array('keytype'=>$keytype,'keyword'=>$keyword));
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $result['total'];	
		$html = $this->html('pop-game-list',$data);
		return $this->json(array('html'=>$html));
	}
}