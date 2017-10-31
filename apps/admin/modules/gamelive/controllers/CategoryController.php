<?php
namespace modules\gamelive\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;

use Youxiduo\Helper\Utility;
use modules\gamelive\models\Article;
use modules\gamelive\models\Video;

class CategoryController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'gamelive';
    }

    public function getList($_channel='article')
    {
        $channel = Input::get('channel',$_channel);
        $data = array();
        $data['channel'] = $channel;
        if($channel=='article'){
            $data['datalist'] = Article::GetArticleTags();
        }elseif($channel=='video'){
            $data['datalist'] = Video::GetVideoTags();
        }

        return $this->display('category-list',$data);
    }

    public function getAdd($channel='article')
    {
        $data = array();
        $data['channel'] = $channel;
        return $this->display('category-add',$data);
    }

    public function getEdit($channel,$_idx)
    {
        $idx = Input::get('idx',$_idx);
        $result = array();
        $data = array();
        $data['channel'] = $channel;
        if($channel=='article'){
            $result = Article::GetArticleTags();
        }elseif($channel=='video'){
            $result = Video::GetVideoTags();
        }
        foreach($result as $row){
            if($row['idx']==$idx){
                $data['category'] = $row;
            }
        }
        return $this->display('category-add',$data);
    }

    public function postEdit()
    {
        $channel = Input::get('channel');
        $tag = Input::get('tag');
        $idx = Input::get('idx',0);

        $success = false;
        if($channel=='article'){
            $success = Article::SaveArticleTag($idx,$tag);
        }elseif($channel=='video'){
            $success = Video::SaveVideoTag($idx,$tag);
        }
        if($success){
            return $this->redirect('gamelive/category/list/'.$channel);
        }else{
            return $this->back('保存失败');
        }
    }

    public function getDelete($channel,$_idx)
    {
        $idx = Input::get('idx', $_idx);
        $result = false;
        $data = array();
        if ($channel == 'article') {
            $result = Article::RemoveArticleTag($idx);
        } elseif ($channel == 'video') {
            $result = Video::RemoveVideoTag($idx);
        }
        if($result){
            return $this->back('删除成功');
        }
        return $this->back('删除失败');
    }
}