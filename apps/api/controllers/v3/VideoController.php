<?php
use Yxd\Services\Cms\VideoService;
use Yxd\Services\Cms\ArticleService;
use Illuminate\Support\Facades\Input;

use Yxd\Modules\Core\CacheService;
/**
 * 资讯
 */
class VideoController extends BaseController
{
	/**
	 * 美女视频
	 */
	public function girl()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$type = Input::get('type',0);
		
	    $section = 'article::video';
		$cachekey = 'article::video::' . $page . '::sort::' . $type;
		CLOSE_CACHE && CacheService::section($section)->flush();
	    if(CacheService::section($section)->has($cachekey)){
			$result = CacheService::section($section)->get($cachekey);
		}else{
			$result = VideoService::getVideoList($page,$pagesize,$type);
		    CacheService::section($section)->forever($cachekey,$result);
		}
		
		return $this->success(array('result'=>$result['videos'],'totalCount'=>$result['total']));
	}
	
	/**
	 * 视频详情
	 */
	public function detail()
	{
		$id = Input::get('vid');
		$uid = Input::get('uid');
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$result = VideoService::getVideoDetail($id,$page,$pagesize);
		if(!$result){
			return $this->fail(11211,'视频不存在');
		}
		return $this->success($result);
	}
}