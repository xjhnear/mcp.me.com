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

class PictureController extends BackendController
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
		$result = Picture::searchList($search,$pageIndex,$pageSize);
		$data['datalist'] = $result['result'];
		$totalCount = $result['totalCount'];
		$pager = Paginator::make(array(),$totalCount,$pageSize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		return $this->display('picture-list',$data);		
	}
	
	public function getAdd()
	{
		$data = array();
		$catalogs = Picture::getAlbumAllCategory(true);
		$data['catalogs'] = $catalogs;
		return $this->display('picture-add',$data);
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
			//return $this->back('请选择要上传的背景图');
		}
		
		$img_names = Input::get('img_name');
		$img_descs = Input::get('img_desc');
		$picInfo = array();
		if($img_names && is_array($img_names)){
			$idx = 1;
			foreach($img_names as $key=>$pic){
				$picInfo[] = $idx.'|'.$pic . '|'.$img_descs[$key];
				$idx++;
			}
			//$picInfo = implode('|',$picInfo);
		}
		$result = Picture::CreateAlbum($title,$subtitle,$source,$author,$gameId,$titlePic,$catalog,$summary,$content,$tags,$picInfo);
		if($result==true){
			return $this->redirect('wcms/picture/index','保存成功');
		}
		return $this->back('保存失败');
	}
	
    public function getEdit()
	{
		$id = Input::get('id');
		$article = Picture::getAlbumDetail($id);
		$article['gameId'] = implode(',',$article['refGameId']);
		$article['tags'] = implode(',',$article['tags']);		
		$data['article'] = $article;
		$catalogs = Picture::getAlbumAllCategory(true);
		$data['catalogs'] = $catalogs;
		return $this->display('picture-edit',$data);
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
			//return $this->back('请选择要上传的背景图');
		}
		
		$img_names = Input::get('img_name');
		$img_descs = Input::get('img_desc');
		$picInfo = array();
		if($img_names && is_array($img_names)){
			$idx = 1;
			foreach($img_names as $key=>$pic){
				$picInfo[] = $idx.'|'.$pic . '|'.$img_descs[$key];
				$idx++;
			}
			//$picInfo = implode('|',$picInfo);
		}
		$result = Picture::UpdateAlbum($id,$title,$subtitle,$source,$author,$gameId,$titlePic,$catalog,$summary,$content,$tags,$picInfo);
		if($result==true){
			return $this->redirect('wcms/picture/index','保存成功');
		}
		return $this->back('保存失败');
	}
	
    public function getDelete()
	{
		$id = Input::get('id');
		$result = Picture::RemoveAlbum($id);
		if($result==true){
			return $this->back('删除成功');
		}else{
			return $this->back('删除失败');
		}
	}
}