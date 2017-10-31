<?php
use Yxd\Modules\Core\CacheService;
use Yxd\Services\ForumService;
use Yxd\Services\Cms\ArticleService;
use Yxd\Services\Cms\InfoService;
use Illuminate\Support\Facades\Input;

/**
 * 游戏
 */
class WcaController extends BaseController
{
	
	
	/**
	 * 攻略列表
	 */
	public function getGuideLists()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$gnid = Input::get('gnid');
		$gnid = explode(',',$gnid);
		$result = InfoService::getNewsListss2($gnid,$page,$pagesize);
		return $this->success($result);
	}
	
	
}