<?php
namespace modules\gamelive\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;

use Youxiduo\Helper\Utility;
use modules\gamelive\models\Video;
use modules\gamelive\models\Navigation;
use modules\gamelive\models\Game;
use modules\gamelive\models\Anchor;

class VideoController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'gamelive';
    }

    public function getList()
    {
        $pageIndex = (int)Input::get('page',1);
        $pageSize = 10;
        $search = array();
        $result = Video::GetVideoList($pageIndex,$pageSize);
        $data['datalist'] = $result['list'];
        $totalCount = $result['totalPage'] * $pageSize;
        $pager = Paginator::make(array(),$totalCount,$pageSize);
        $pager->appends($search);
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        return $this->display('video-list',$data);
    }


    public function getEdit()
    {
        $id = Input::get('id');
        if($id){
            $article = Video::getVideoDetail($id);
            $article['refGameId'] = implode(',',$article['refGameId']);
            $article['refPeopleId'] = implode(',',$article['refPeopleId']);
            $article['tags'] = implode(',',$article['tags']);
            $data['article'] = $article;
        }

        $_catalogs = Video::GetVideoCatalogs();

        /*
        foreach($_catalogs as $row){
            $catalogs[$row['url']] = $row['name'];
        }
        $data['catalogs'] = $catalogs;
        */


        if(isset($data['article']['refGameId'])){
            $gameIds = $data['article']['refGameId'];
            if($gameIds){
                $gameNames = Game::GetGameNames($gameIds);
                if($gameNames){
                    $data['article']['gameNames'] = $gameNames;
                }
            }
        }
        if(isset($data['article']['refPeopleId'])){
            $peopleIds = $data['article']['refPeopleId'];
            if($peopleIds){
                $peopleNames = Anchor::GetPeopleNames($peopleIds);
                if($peopleNames){
                    $data['article']['peopleNames'] = $peopleNames;
                }
            }
        }
        
        $data['columns'] = Navigation::GetColumnOptions();
        return $this->display('video-edit',$data);
    }

    public function postEdit()
    {
        $id = Input::get('id');
        $title = Input::get('title');
        $subtitle = Input::get('subtitle');
        $titlePic = Input::get('titlePic');
        $gameId = Input::get('gameId');
        $peopleId = Input::get('peopleId');
        $catalog = Input::get('catalog');
        $columnId = Input::get('columnId');
        $summary = Input::get('summary');
        $content = Input::get('content');
        $tags = Input::get('tags');
        $publishTime = strtotime(Input::get('publishTime'));
        $tags = explode(',',$tags);
        $gameId = explode(',',$gameId);
        $peopleId = explode(',',$peopleId);

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
            'titlePic'=>'required',
            'summary'=>'required',
            'content'=>'required'
        );
        $prompt = array(
            'title.required'=>'标题不能为空',
            'subtitle.required'=>'短标题不能为空',
            'titlePic.required'=>'图片不能为空',
            'summary.required'=>'概要不能为空',
            'content.required'=>'内容不能为空',
        );
        $valid = Validator::make($input,$rules,$prompt);
        if($valid->fails()){
            return $this->back($valid->messages()->first());
        }
        $result = false;
        if($id){
            $result = Video::UpdateVideo($id,$title,$subtitle,$titlePic,$publishTime,$catalog,$columnId,$gameId,$summary,$tags,$peopleId,$content);
        }else{
            $result = Video::CreateVideo($title,$subtitle,$titlePic,$publishTime,$catalog,$columnId,$gameId,$summary,$tags,$peopleId,$content);
        }

        if($result==true){
            return $this->redirect('gamelive/video/list','修改视频成功');
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