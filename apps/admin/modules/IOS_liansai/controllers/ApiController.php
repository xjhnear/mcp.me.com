<?php
namespace modules\yxvl_eSports\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Cms\WebGameService;

use modules\wcms\models\Article;
use modules\wcms\models\Picture;
use modules\wcms\models\Video;

class ApiController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'yxvl_eSports';
	}
	
	public function getTags()
	{
		$data = array(
		    array('id'=>'1','label'=>'网络游戏','value'=>'网络游戏'),
		    array('id'=>'2','label'=>'单机游戏','value'=>'单机游戏'),
		    array('id'=>'3','label'=>'益智游戏','value'=>'益智游戏'),
		    array('id'=>'4','label'=>'竞技体育','value'=>'竞技体育'),
		    array('id'=>'5','label'=>'三国','value'=>'三国'),
		    array('id'=>'6','label'=>'西游','value'=>'西游'),
		);
		return $this->json($data);
	}
	
	public function getArticleSearch()
	{
		$keyword = Input::get('keyword');
		$search = array('keyword'=>$keyword);
		$pageIndex = Input::get('page',1);
		$pageSize = 10;
		$result = Article::searchList($search,$pageIndex,$pageSize);
		$data['datalist'] = $result['result'];
		$totalCount = $result['totalCount'];
		$pager = Paginator::make(array(),$totalCount,$pageSize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $totalCount;
		$html = $this->html('pop-article-list',$data);
		return $this->json(array('html'=>$html));
	}
	
	public function getPopArticleSearch()
	{
		$keyword = Input::get('keyword');
		$search = array('keyword'=>$keyword);
		$pageIndex = Input::get('page',1);
		$pageSize = 10;
		$result = Article::searchList($search,$pageIndex,$pageSize);
		$data['datalist'] = $result['result'];
		$totalCount = $result['totalCount'];
		$pager = Paginator::make(array(),$totalCount,$pageSize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $totalCount;	
		$html = $this->html('pop-article-list',$data);
		return $this->json(array('html'=>$html));
	}
	
    public function getPopPictureSearch()
	{
		$keyword = Input::get('keyword');
		$search = array('keyword'=>$keyword);
		$pageIndex = Input::get('page',1);
		$pageSize = 10;
		$result = Picture::searchList($search,$pageIndex,$pageSize);
		$data['datalist'] = $result['result'];
		$totalCount = $result['totalCount'];
		$pager = Paginator::make(array(),$totalCount,$pageSize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $totalCount;	
		$html = $this->html('pop-picture-list',$data);
		return $this->json(array('html'=>$html));
	}
	
    public function getPopVideoSearch()
	{
		$keyword = Input::get('keyword');
		$search = array('keyword'=>$keyword);
		$pageIndex = Input::get('page',1);
		$pageSize = 10;
		$result = Video::searchList($search,$pageIndex,$pageSize);
		$data['datalist'] = $result['result'];
		$totalCount = $result['totalCount'];
		$pager = Paginator::make(array(),$totalCount,$pageSize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $totalCount;	
		$html = $this->html('pop-video-list',$data);
		return $this->json(array('html'=>$html));
	}
	
	public function getPopGameSearch()
	{
		$keyword = Input::get('keyword');
		$search = array();
		if(is_numeric($keyword)){
			$search['id'] = $keyword;
		}else{
			$search['keyword'] = $keyword;
		}
		$pageIndex = Input::get('page',1);
		$pageSize = 10;
		$result = WebGameService::searchGameList($search,$pageIndex,$pageSize,array(),true);
		$data['datalist'] = $result['result'];
		$totalCount = $result['totalCount'];
		$pager = Paginator::make(array(),$totalCount,$pageSize);
		$pager->appends(array('keyword'=>$keyword));
		$data['keyword'] = $keyword;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $totalCount;	
		$html = $this->html('pop-game-list',$data);
		return $this->json(array('html'=>$html));
	}
}