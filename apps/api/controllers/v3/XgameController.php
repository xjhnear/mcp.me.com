<?php
use Yxd\Modules\Core\CacheService;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Yxd\Services\Cms\XgameService;

/**
 * 游戏
 */
class XgameController extends BaseController
{
	
	
	/**
	 * 游戏详情
	 */
	public function article()
	{
		$gid = Input::get('gid');
	 	if(!$gid){
			return $this->fail(1121,'参数错误');
		}
		$result = XgameService::getArticle($gid);
		return $this->success($result);
	}
	
	/**
	 * 小游戏列表
	 */
	public function getlist()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$type = Input::get('type',0);
		$keyword = Input::get('keyword','');
		//查询游戏
		$result = XgameService::getList($page,$pagesize,$type,$keyword);
		
		return $this->success(array('result'=>$result['games'],'totalCount'=>$result['total'],'typetitle'=>$result['typetitle']));
	}
	
	/**
	 * 增加热度
	 */
	public function doHot()
	{
		$gid = Input::get('gid',1);
		if(!$gid){
			return $this->fail(1121,'接口参数丢失');
		}
		$result = XgameService::doHot($gid);
		if($result===true){
			return $this->success(array('result'=>true));
		}else{
			return $this->success(array('result'=>false));
		}
	}
	
	/**
	 * banner图list
	 */
	public function getBannerList() {
		$sortAsc = Input::get('sortAsc',1);
		$sortAsc = $sortAsc ? true : false ;
		$result = XgameService::getBanner('',$sortAsc);
		return $this->success(array('result'=>$result['result'],'totalCount'=>$result['total']));
	}
	/**
	 * 小游戏访问统计
	 */
	public function anyCount(){
		$input = Input::all();
		$data['ip'] = $input['ip'];
		$data['gid'] = empty($input['gid']) ? 0 : $input['gid'];
		$data['url'] = $input['url'] ? $input['url'] : '';
		$beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
		$data['addtime'] = time();
		$data['dtime'] = $beginToday;
		$data['platform'] = $input['platform'];
		if($data['gid'] == 0 && $data['url'] == ''){
			$rs = false;
		}else{
			$rs = XgameService::doSaveCount($data);
		}
		return $this->success(array('result'=>$rs));
	}
	
	
}