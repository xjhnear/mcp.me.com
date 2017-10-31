<?php
namespace modules\gamelive\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;

use Youxiduo\Helper\Utility;
use modules\gamelive\models\Anchor;


class AnchorController extends BackendController
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
        $result = Anchor::GetPeopleList($page,$size);
        $data['datalist'] = $result['list'];
        $pager = Paginator::make(array(),$result['totalPage']*$size,$size);
        $pager->appends($search);
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        return $this->display('anchor-list',$data);
    }

    public function getEdit($_id=null)
    {
        $id = Input::get('id',$_id);
        $data = array();
        $data['pics'] = json_encode(array());
        if($id){
            $result = Anchor::GetPeopleDetail($id);
            $result['tags'] = implode(',',$result['tags']);
            $data['pics'] = json_encode($result['albums']);
            $data['anchor'] = $result;
        }
        return $this->display('anchor-edit',$data);
    }

    public function postEdit()
    {
        $id = Input::get('id');
        $name = Input::get('name');
        $picUrl = Input::get('picUrl');
        $summary = Input::get('summary');
        $idx = Input::get('idx',0);
        $publishTime = time();
        $thumbnail = Input::get('thumbnail');

        $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/' . date('d') . '/';
        $path = storage_path() . $dir;
        //列表图
        if(Input::hasFile('fileavatar')){

            $file = Input::file('fileavatar');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $picUrl = $dir . $new_filename . '.' . $mime;
            $picUrl = Utility::getImageUrl($picUrl);
        }

        //列表图
        if(Input::hasFile('filepic')){

            $file = Input::file('filepic');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $thumbnail = $dir . $new_filename . '.' . $mime;
            $thumbnail = Utility::getImageUrl($thumbnail);
        }

        $picAlbum = null;
        $img_names = Input::get('img_name');
        if($img_names && is_array($img_names)){

            foreach($img_names as $key=>$pic){
                $pics[] = $pic;
            }
            $picAlbum = implode(';',$pics);
        }

        $result = false;
        if($id){
            $result = Anchor::UpdatePeople($id,$name,$picUrl,$summary,$idx,$publishTime,null,null,$picAlbum,$thumbnail);
        }else{
            $result = Anchor::CreatePeople($name,$picUrl,$summary,$idx,$publishTime,null,null,$picAlbum,$thumbnail);
        }
        if($result){
            return $this->redirect('gamelive/anchor/list');
        }else{
            return $this->back('保存失败');
        }
    }

    public function getDelete()
    {
        $id = Input::get('id');
        $result = Anchor::RemovePeople($id);
        if($result==true){
            return $this->back('删除成功');
        }else{
            return $this->back('删除失败');
        }
    }
}