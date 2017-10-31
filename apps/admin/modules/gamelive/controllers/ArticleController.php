<?php
namespace modules\gamelive\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;

use Youxiduo\Helper\Utility;
use modules\gamelive\models\Article;


class ArticleController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'gamelive';
    }

    public function getList()
    {
        $page = Input::get('page',1);
        $size = 10;
        $keyword = Input::get('keyword');
        $pageSize = $size;
        $search = array('keyword'=>$keyword);
        $data = array();
        $result = Article::GetArticleList($page,$size,null,null,$keyword);
        $data['datalist'] = $result['list'];
        $pager = Paginator::make(array(),$result['totalPage']*$pageSize,$pageSize);
        $pager->appends($search);
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        return $this->display('article-list',$data);
    }

    public function getEdit()
    {
        $id = Input::get('id');
        if($id){
            $article = Article::getArticleDetail($id);
            $article['refGameId'] = implode(',',$article['refGameId']);
            $article['tags'] = implode(',',$article['tags']);
            $data['article'] = $article;
        }
        $_catalogs = Article::GetArticleCatalogs();
        foreach($_catalogs as $row){
            $catalogs[$row['url']] = $row['name'];
        }
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
        $skipTitle = Input::get('skipTitle');
        $skipUrl = Input::get('skipUrl');
        $catalog = Input::get('catalog');
        $columnId = Input::get('columnId');
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
        $result = false;
        if($id){
            $result = Article::UpdateArticle($id,$title,$subtitle,$source,$author,$columnId,$gameId,$titlePic,$catalog,$summary,$content,$tags,$skipTitle,$skipUrl);
        }else{
            $result = Article::CreateArticle($title,$subtitle,$source,$author,$columnId,$gameId,$titlePic,$catalog,$summary,$content,$tags,$skipTitle,$skipUrl);
        }
        if($result){
            return $this->redirect('gamelive/article/list','修改文章成功');
        }else{
            return $this->back('修改文章失败');
        }

    }

    public function getDelete()
    {
        $id = Input::get('id');
        $result = Article::RemoveArticle($id);
        if($result==true){
            return $this->back('删除成功');
        }else{
            return $this->back('删除失败');
        }
    }

}