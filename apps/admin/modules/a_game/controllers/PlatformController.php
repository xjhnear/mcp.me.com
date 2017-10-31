<?php
namespace modules\a_game\controllers;

use Youxiduo\Android\Model\GamePackageCollect;

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
use Youxiduo\Android\Model\GamePlatform;

class PlatformController  extends BackendController
{
	
	public function _initialize()
	{
		$this->current_module = 'a_game';
	}
	
	public function getIndex($game_id=0)
	{
		$page = Input::get('page',1);
		$pagesize = 10;
		$data = array();
		$search['game_id'] = $game_id;
		$data['game_id'] = $game_id;
		$result = GamePlatform::m_search($search);
		$data['datalist'] = $result['result'];		
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$pager->appends($search);				
		$data['pagelinks'] = $pager->links();		
		return $this->display('platform_list',$data);
	}
	
	public function getAdd($game_id)
	{
		$data = array();
		$data['platform_list'] = Config::get('yxd.platform_list',array());
		$data['game_id'] = $game_id;
		return $this->display('platform_info',$data);
	}
	
    public function getEdit($id)
	{
		$data = array();		
		$data['platform_list'] = Config::get('yxd.platform_list',array());
		$data['platform'] = GamePlatform::m_getInfo($id);
		return $this->display('platform_info',$data);
	}
	
    public function postSave()
	{
		$input = Input::only('id','game_id','platform_id','download_url','size','version','packagename','is_top','is_show','sort');
		if(!$input['is_top']){
			$input['is_top'] = 0;
		}
		
	    if(!$input['is_show']){
			$input['is_show'] = 0;
		}
		
		if($input['platform_id']){
			$platform_list = Config::get('yxd.platform_list',array());
			$input['platform_name'] = $platform_list[$input['platform_id']];
		}
		$input['update_time'] = time();
		GamePlatform::m_save($input);
		
		return $this->redirect('a_game/platform/index/'.$input['game_id'],'游戏平台添加成功');
	}
	
	public function getDelete($id)
	{
		GamePlatform::m_delete($id);
		return $this->back('删除成功');
	}
}