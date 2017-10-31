<?php
namespace modules\forum\controllers;
use Yxd\Modules\Core\BackendController;
use Yxd\Services\Cms\GameService;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use modules\forum\models\ForumModel;

class ApiController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'forum';
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
		$data['imgurl'] = 'http://img.youxiduo.com';
		$result = ForumModel::search($search,$page,$pagesize);			
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