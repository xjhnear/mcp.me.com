<?php
namespace modules\gamelive\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;

use Youxiduo\Helper\Utility;
use modules\gamelive\models\Game;


class GameController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'gamelive';
    }

    public function getList()
    {
        $page = Input::get('page',1);
        $size = 10;
        $search = array();
        $data = array();
        $result = Game::GetGameList($page,$size);
        $data['datalist'] = $result['list'];
        $pager = Paginator::make(array(),$result['totalPage']*$size,$size);
        $pager->appends($search);
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        return $this->display('game-list',$data);
    }

    public function getEdit($_id=null)
    {
        $id = Input::get('id',$_id);
        $data = array();
        if($id){
            $data['game'] = Game::GetGameDetail($id);
        }
        return $this->display('game-edit',$data);
    }

    public function postEdit()
    {
        $id = Input::get('id');
        $name = Input::get('name');
        $gicon = Input::get('gicon');
        $titlePic = Input::get('titlePic');
        $summary = Input::get('summary');
        $top = Input::get('top');
        $publishTime = time();
        $defVideo = Input::get('defVideo','');

        $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
        $path = storage_path() . $dir;
        //列表图
        if(Input::hasFile('file_icon')){

            $file = Input::file('file_icon');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $gicon = $dir . $new_filename . '.' . $mime;
            $gicon = Utility::getImageUrl($gicon);
        }

        $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
        $path = storage_path() . $dir;
        //列表图
        if(Input::hasFile('file_pic')){

            $file = Input::file('file_pic');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $titlePic = $dir . $new_filename . '.' . $mime;
            $titlePic = Utility::getImageUrl($titlePic);
        }

        $result = false;
        if($id){
            $result = Game::UpdateGame($id,$name,$gicon,$titlePic,$summary,$top,$defVideo,$publishTime);
        }else{
            $result = Game::CreateGame($name,$gicon,$titlePic,$summary,$top,$defVideo,$publishTime);
        }
        if($result){
            return $this->redirect('gamelive/game/list');
        }else{
            return $this->back('保存失败');
        }
    }

    public function getDelete()
    {
        $id = Input::get('id');
        $result = Game::RemoveGame($id);
        if($result==true){
            return $this->back('删除成功');
        }else{
            return $this->back('删除失败');
        }
    }
}