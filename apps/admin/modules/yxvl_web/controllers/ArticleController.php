<?php
namespace modules\yxvl_eSports\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelpLx;
use modules\wcms\models\Article;
use modules\yxvl_eSports\controllers\HelpController;
use Youxiduo\ESports\ESportsService;

class ArticleController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'yxvl_eSports';
	}
	
	public function getIndex()
	{
		$pageIndex = (int)Input::get('page',1);
		$keyword = Input::get('keyword');
		$pageSize = 10;
		$search = array('titleContain'=>$keyword,'size'=>$pageSize,'page'=>$pageIndex);
		$res = ESportsService::excute($search,"GetArticleList",true);
        if($res['data']){
            $data['datalist'] = $res['data']['list'];
            $totalPage = $res['data']['totalPage'];
        }
        $data['search'] = $search;
        unset($search['page']);//pager不能有‘page'参数
		$data['pagelinks'] = MyHelpLx::pager(array(),$totalPage*$pageSize,$pageSize,$search);
		
		return $this->display('article-list',$data);
	}
	
	public function getAdd()
	{
		$data = array('catalogs'=>array());
        $data['catalogs'] = HelpController::getCategoryArr($data);
        $id = Input::get('id',"");
        if($id){
            $res = ESportsService::excute(array('id'=>$id),"GetArticleDetail",true);
            if($res['data']){
                $data['data'] = $res['data'];
                $data['data']['publishTime'] = date('Y-m-d H:i:s',$data['data']['publishTime']);
                $data['data']['tags'] = implode(',',$data['data']['tags']);
            }
        }
		return $this->display('article-add',$data);
	}

    public function postAjaxDel()
    {
        $data = Input::all();
        $res = ESportsService::excute($data,"RemoveArticle",false);
        echo json_encode($res);
    }

	public function postAdd()
	{
        $id = Input::get("id");
        $input = Input::all();
        $input['tag'] = explode(',',Input::get('tag'));
        $img = MyHelpLx::save_img($input['titlePic']);
        $input['titlePic'] =$img ? $img:$input['titleImg'];unset($input['titleImg']);
        $input['editor'] = $this->current_user['authorname'];
        $input['publishTime'] = strtotime($input['publishTime']);

        if($id){
            $res= ESportsService::excute2($input,"UpdateArticle",false);
        }else{
            unset($input['id']);
            $res= ESportsService::excute2($input,"CreateArticle",true);
            if($res['success']){
                $urls = array(
                    'http://dj.vlong.tv/article/'.$res['data'].'.html',
                );
                $api = 'http://data.zz.baidu.com/urls?site=dj.vlong.tv&token=bQ4PZLCdJmpp2asj&type=original';
                $result = MyHelpLx::baidu_weburl($urls,$api);
                if(isset($result['error'])){
                    $str = $result['error'].':'.$result['message'];
                    return $this->redirect('yxvl_eSports/article/index','添加成功，但是推送百度失败，请尝试手动添加！(错误码'.$str.')');
                    $dir = '/logs/baidu_webs_error_log.txt';
                    $path = storage_path() . $dir;
                    MyHelpLx::error_log($path,$str.";http://dj.vlong.tv/article/".$res['data'].".html\r\n");
                }else{
                    $dir = '/logs/baidu_webs_error_log.txt';
                    $path = storage_path() . $dir;
                    MyHelpLx::error_log($path,"推送成功：http://dj.vlong.tv/article/".$res['data'].".html\r\n");
                }
            }
        }
		if($res){
			return $this->redirect('yxvl_eSports/article/index','添加成功');
		}else{
			return $this->back('添加失败');
		}
	}
	
    public function getEdit()
	{
		$id = Input::get('id');
		$article = Article::getArticleDetail($id);
		$article['gameId'] = implode(',',$article['refGameId']);
		$article['tags'] = implode(',',$article['tags']);		
		$data['article'] = $article;
		$catalogs = Article::getArticleAllCategory(true);
		$data['catalogs'] = $catalogs;
		return $this->display('article-edit',$data);
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
		$publishTime = Input::get('publishTime');
		if($publishTime){
			$publishTime = strtotime($publishTime);
		}
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
			'source'=>'required',
			'author'=>'required',
			'titlePic'=>'required',
			'summary'=>'required',
			'content'=>'required'
		);
		$prompt = array(
			'title.required'=>'标题不能为空',
		    'subtitle.required'=>'短标题不能为空',
		    'source.required'=>'来源不能为空',
			'author.required'=>'作者不能为空',
			'titlePic.required'=>'图片不能为空',
			'summary.required'=>'概要不能为空',
			'content.required'=>'内容不能为空',
		);
		$valid = Validator::make($input,$rules,$prompt);
		if($valid->fails()){
			return $this->back($valid->messages()->first());
		}
		$args = array();
		$args['publishTime'] = $publishTime;
		$args['editor'] = $this->current_user['authorname'];
		$result = Article::updateArticle($id,$title,$subtitle,$source,$author,$gameId,$titlePic,$albumId,$videoId,$catalog,$summary,$content,$tags,$args);
		if($result==true){
			$this->recordLog('修改了《'.$title.'》');
			return $this->redirect('yxvl_eSports/article/index','修改成功');
		}else{
			return $this->back($result['error']);
		}
		
	}
	
	public function getView()
	{
		$id = Input::get('id');
		return $this->back('功能还在开发中');
	}
	
    public function getDelete()
	{
		$id = Input::get('id');
		$result = Article::RemoveArticle($id);
		if($result==true){
			$this->recordLog('删除了,ID：'.$id.'');
			return $this->back('删除成功');
		}else{
			return $this->back('删除失败');
		}
	}
}