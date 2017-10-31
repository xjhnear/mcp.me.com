<?php
namespace modules\a_game\controllers;

use Youxiduo\Android\Model\GamePackageCollect;
use Youxiduo\Android\Model\GamePackageMatchHistory;
use Youxiduo\Android\Model\UserPackage;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;

use Yxd\Modules\Core\BackendController;


use Youxiduo\Android\Model\Game;

class PkgController extends BackendController
{
	
	public function _initialize()
	{
		$this->current_module = 'a_game';
	}
	
	public function getWait()
	{
		$page = Input::get('page',1);
		$pagesize = 10;
		$data = array();
		$pkgs = UserPackage::getAllPackage();		
		$result = GamePackageCollect::m_searchGameListByPackage($pkgs,$page,$pagesize);
		$match_pkg = GamePackageMatchHistory::getMatchCountByPackageName($pkgs);
		$data['datalist'] = $result['result'];
		$data['match_pkg'] = $match_pkg;
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		//$pager->appends($cond);
		$data['pagelinks'] = $pager->links();
		return $this->display('wait_game',$data);
	}
}