<?php
namespace modules\a_game\controllers;
use Yxd\Modules\Core\BackendController;
use Yxd\Services\Cms\GameService;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;

use Youxiduo\Android\Model\Game;
use Youxiduo\Android\Model\UserGame;
class ApiController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'a_game';
	}
	
	public function getGameSelectSearch()
	{
		$out = array();
		$keyword = Input::get('q');
		$search = array();
		if($keyword){
			$search['gname'] = $keyword;
		}
		$result = Game::m_search($search,1,20);
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
			$game = Game::m_getInfo($id);
			if($game){
				return $this->json(array('id'=>$game['id'],'text'=>$game['shortgname']));
			}
		}
	}
	
	public function getGameUsers()
	{
	    $game_id = Input::get('game_id');
		if($game_id){
		    $uids = UserGame::getAllUserId($game_id);
			if($uids){
			    return implode(',',$uids);
			}
		}
		return '';
	}
	
    public function getGameSearch()
	{
		$keytype = Input::get('keytype','id');
		$keyword = Input::get('keyword');
		$search = $vsearch = array();
		if($keytype=='id'){
            $search['id'] = $keyword;
			$vsearch['keyword'] = $keyword;
            $vsearch['keytype'] = 'id';
		}else{
            $search['gname'] = $keyword;
            $vsearch['keytype'] = 'gname';
			$vsearch['keyword'] = $keyword;
		}
		
		$page = Input::get('page',1);
		$pagesize = 6;
		$data = array();	
		$data['keytype'] = $keytype;
		$data['keyword'] = $keyword;
		$data['gametype'] = array('0'=>'未分类')+(GameService::getGameTypeOption());
		$data['pricetype'] = Config::get('yxd.game_pricetype');
		$data['zonetype'] = Config::get('yxd.game_zonetype');
		$data['imgurl'] = 'http://img.youxiduo.com';
		$result = Game::m_search($search,$page,$pagesize);			
		$data['games'] = $result['result'];
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$pager->appends($vsearch);
		$data['search'] = $vsearch;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $result['total'];	
		$html = $this->html('pop-game-list',$data);
		return $this->json(array('html'=>$html));
	}
}