<?php
namespace modules\wcms\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;

use Youxiduo\Helper\Utility;
use modules\wcms\models\Article;
use modules\wcms\models\Picture;
use modules\wcms\models\Video;

class VideoController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'wcms';
	}
	
	public function getIndex()
	{
		$pageIndex = (int)Input::get('page',1);
		$pageSize = 10;
		$search = array();
		$result = Video::searchList($search,$pageIndex,$pageSize);
		$data['datalist'] = $result['result'];
		$totalCount = $result['totalCount'];
		$pager = Paginator::make(array(),$totalCount,$pageSize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		return $this->display('video-list',$data);		
	}
	
	public function getAdd()
	{
		$data = array();
		$catalogs = Video::getAllCategory(true);
		$data['catalogs'] = $catalogs;
		return $this->display('video-add',$data);
	}
	
	public function postAdd()
	{
		$title = Input::get('title');
		$subtitle = Input::get('subtitle');
		$source = Input::get('source');
		$author = Input::get('author');
		$titlePic = Input::get('titlePic');
		$gameId = Input::get('gameId');
		$videoId = Input::get('videoId');
		$albumId = Input::get('albumId');
		$catalog = Input::get('catalog');
		$summary = Input::get('summary');
		$content = Input::get('content');
		$tags = Input::get('tags');
		
		$tags = explode(',',$tags);
		$gameId = explode(',',$gameId);
		
	    $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    //列表图
	    if(Input::hasFile('filedata')){
	    	
			$file = Input::file('filedata'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$titlePic = $dir . $new_filename . '.' . $mime;
			$titlePic = Utility::getImageUrl($titlePic);
		}else{
			return $this->back('请选择要上传的背景图');
		}
		$input = Input::all();
		$input['titlePic'] = $titlePic;
		$rules = array(
			'title'=>'required',
			'subtitle'=>'required',
			//'source'=>'required',
			//'author'=>'required',
			'titlePic'=>'required',
			'summary'=>'required',
			'content'=>'required'
		);
		$prompt = array(
			'title.required'=>'标题不能为空',
		    'subtitle.required'=>'短标题不能为空',
		    //'source.required'=>'来源不能为空',
			//'author.required'=>'作者不能为空',		    
			'titlePic.required'=>'图片不能为空',
			'summary.required'=>'概要不能为空',
			'content.required'=>'内容不能为空',
		);
		$valid = Validator::make($input,$rules,$prompt);
		if($valid->fails()){
			return $this->back($valid->messages()->first());
		}
	    $result = Video::CreateVideo($title,$subtitle,$source,$author,$gameId,$titlePic,$catalog,$summary,$content,$tags);
		if($result==true){
			return $this->redirect('wcms/video/index','添加视频成功');
		}else{
			return $this->back('添加视频失败');
		}
	}
	
    public function getEdit()
	{
		$id = Input::get('id');
		$article = Video::getVideoDetail($id);
		$article['gameId'] = implode(',',$article['refGameId']);
		$article['tags'] = implode(',',$article['tags']);		
		$data['article'] = $article;
		$catalogs = Video::getAllCategory(true);
		$data['catalogs'] = $catalogs;
		return $this->display('video-edit',$data);
	}
	
	public function postEdit()
	{
		$id = Input::get('id');
		$title = Input::get('title');
		$subtitle = Input::get('subtitle');
		$source = Input::get('source');
		$author = Input::get('author');
		$titlePic = Input::get('titlePic');
		$gameId = Input::get('gameId');
		$catalog = Input::get('catalog');
		$summary = Input::get('summary');
		$content = Input::get('content');
		$tags = Input::get('tags');
		$publishTime = strtotime(Input::get('publishTime'));
		$tags = explode(',',$tags);
		$gameId = explode(',',$gameId);
		
	    $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    //列表图
	    if(Input::hasFile('filedata')){
	    	
			$file = Input::file('filedata'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$titlePic = $dir . $new_filename . '.' . $mime;
			$titlePic = Utility::getImageUrl($titlePic);
		}
		
	    $input = Input::all();
		$input['titlePic'] = $titlePic;
		$rules = array(
			'title'=>'required',
			'subtitle'=>'required',
			//'source'=>'required',
			//'author'=>'required',
			'titlePic'=>'required',
			'summary'=>'required',
			'content'=>'required'
		);
		$prompt = array(
			'title.required'=>'标题不能为空',
		    'subtitle.required'=>'短标题不能为空',
		    //'source.required'=>'来源不能为空',
			//'author.required'=>'作者不能为空',		    
			'titlePic.required'=>'图片不能为空',
			'summary.required'=>'概要不能为空',
			'content.required'=>'内容不能为空',
		);
		$valid = Validator::make($input,$rules,$prompt);
		if($valid->fails()){
			return $this->back($valid->messages()->first());
		}
		
		$result = Video::updateVideo($id,$title,$subtitle,$source,$author,$gameId,$titlePic,$catalog,$summary,$content,$tags,$publishTime);
		if($result==true){
			return $this->redirect('wcms/video/index','修改视频成功');
		}else{
			return $this->back('修改视频失败');
		}
	}
	
	public function getDelete()
	{
		$id = Input::get('id');
		$result = Video::RemoveVideo($id);
		if($result==true){
			return $this->back('删除成功');
		}else{
			return $this->back('删除失败');
		}
	}
}