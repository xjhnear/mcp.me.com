<?php
use Yxd\Modules\Core\CacheService;
use Yxd\Services\ForumService;
use Yxd\Services\Cms\ArticleService;
use Yxd\Services\Cms\InfoService;
use Illuminate\Support\Facades\Input;
/**
 * 资讯
 */
class ArticleController extends BaseController
{
	/**
	 * 资料大全首页
	 */
	public function home()
	{
		$gameid = Input::get('gid');
		if(!$gameid){
			return $this->fail(1121,'gid参数不能为空');
		}
		
		$out = ArticleService::getArticleHome($gameid);		
		return $this->success(array('result'=>$out));
	}
	
	/**
	 * 新闻
	 */
	public function news()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$sort = Input::get('type',0);
		
		$section = 'article::news';
		$cachekey = 'article::news::' . $page . '::sort::' . $sort;
		if(CLOSE_CACHE===false && CacheService::section($section)->has($cachekey)){
			$result = CacheService::section($section)->get($cachekey);
		}else{
		    $result = InfoService::getNewsList($page,$pagesize,$sort);
		    CLOSE_CACHE===false && CacheService::section($section)->forever($cachekey,$result);
		}
		return $this->success($result);
	}
	
	/**
	 * 攻略合集
	 */
	public function guide_collect()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$sort = Input::get('type',0);
		
		$section = 'article::guide_collect';
		$cachekey = 'article::guide_collect::' . $page . '::sort::' . $sort;
	    if(CLOSE_CACHE===false && CacheService::section($section)->has($cachekey)){
			$result = CacheService::section($section)->get($cachekey);
		}else{
		    $result = InfoService::getGuideCollect($page,$pagesize,$sort);
		    CLOSE_CACHE===false && CacheService::section($section)->forever($cachekey,$result);
		}
		
		return $this->success($result);
	}
	
    /**
	 * 攻略列表
	 */
	public function guide_list()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$guid = Input::get('guid');
		$gnid = Input::get('gnid');
		$goid = Input::get('goid');
		if($guid){
		    $result = InfoService::getGuideList($guid,$page,$pagesize);
		}elseif($gnid){
			$result = InfoService::getNewsList2($gnid,$page,$pagesize);
		}elseif($goid){
			$result = InfoService::getOpinionList2($goid,$page,$pagesize);
		}
		return $this->success($result);
	}
	
	/**
	 * 评测
	 */
	public function opinion()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$sort = Input::get('type',0);
		
	    $section = 'article::opinion';
		$cachekey = 'article::opinion::' . $page . '::sort::' . $sort;
	    if(CLOSE_CACHE===false && CacheService::section($section)->has($cachekey)){
			$result = CacheService::section($section)->get($cachekey);
		}else{
		    $result = InfoService::getOpinionList($page,$pagesize,$sort);
		    CLOSE_CACHE===false && CacheService::section($section)->forever($cachekey,$result);
		}
		
		return $this->success($result);
	}
	
	/**
	 * 详情
	 */
	public function detail()
	{
		$id = Input::get('aid');
		$typeid = Input::get('typeID');
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$uid = Input::get('uid',0);
		
		$out = array();
		switch($typeid){
			case InfoService::NEWS:
				$out = InfoService::getNewsDetail($id,$page,$pagesize);
				break;
			case InfoService::GUIDE:
				$out = InfoService::getGuideDetail($id,$page,$pagesize);
				break;
			case InfoService::OPINION:
				$out = InfoService::getOpinionDetail($id,$page,$pagesize);
				break;
			case InfoService::NEWGAME:
				$out = InfoService::getNewGameDetail($id,$page,$pagesize);
				break;
			case InfoService::TOPIC:
				$out = ForumService::getTopicDetail($id,$page,$pagesize,$uid);
				break;				
		}
		return $this->success($out);		
	}	
}