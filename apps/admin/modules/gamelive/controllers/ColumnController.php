<?php
namespace modules\gamelive\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;

use Youxiduo\Helper\Utility;
use modules\gamelive\models\Navigation;
use modules\gamelive\models\Anchor;


class ColumnController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'gamelive';
    }

    public function getList()
    {
        $page = Input::get('page', 1);
        $size = 10;
        $search = array();
        $data = array();
        $result = Navigation::GetColumnList($page,$size);
        $data['datalist'] = $result['list'];
        $pager = Paginator::make(array(), $result['totalPage'] * $size, $size);
        $pager->appends($search);
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        return $this->display('column-list', $data);
    }

    public function getEdit($_id=null)
    {
        $id = Input::get('id',$_id);
        $data = array();
        if($id){
            $column = Navigation::GetColumnDetail($id);
            $column['tags'] = implode(',',$column['tags']);
            $column['peoples'] = implode(',',$column['peoples']);
            $data['column'] = $column;
        }
        if(isset($data['column']['peoples'])){
            $peopleIds = $data['column']['peoples'];
            if($peopleIds){
                $peopleNames = Anchor::GetPeopleNames($peopleIds);
                if($peopleNames){
                    $data['column']['peopleNames'] = $peopleNames;
                }
            }
        }

        return $this->display('column-edit',$data);
    }

    public function postEdit()
    {
        $id = Input::get('id');

        $name = Input::get('name');
        $picUrl = Input::get('picUrl');
        $thumbnail = Input::get('thumbnail');
        $summary = Input::get('summary');
        $content = Input::get('content');
        $publishTime = time();
        $tags = Input::get('tags');
        $tags = explode(',',$tags);

        $peoples = Input::get('peoples');
        $peoples = explode(',',$peoples);

        $albums = Input::get('albums');
        $albums = explode(',',$albums);

        $H5Code = Input::get('H5Code');
        $PCCode = Input::get('PCCode');
        $shareTitle = Input::get('shareTitle');
        $sharePicUrl = Input::get('sharePicUrl');
        $shareSummary = Input::get('shareSummary');


        $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
        $path = storage_path() . $dir;
        //列表图
        if(Input::hasFile('filedata')){

            $file = Input::file('filedata');
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

        //列表图
        if(Input::hasFile('fileshare')){

            $file = Input::file('fileshare');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $sharePicUrl = $dir . $new_filename . '.' . $mime;
            $sharePicUrl = Utility::getImageUrl($sharePicUrl);
        }


        $result = false;
        if($id){
            $result = Navigation::UpdateColumn($id,$name,$picUrl,$summary,$content,$publishTime,$tags,$albums,$peoples,$thumbnail,$H5Code,$PCCode,$shareTitle,$sharePicUrl,$shareSummary);

        }else{
            $result = Navigation::CreateColumn($name,$picUrl,$summary,$content,$publishTime,$tags,$albums,$peoples,$thumbnail,$H5Code,$PCCode,$shareTitle,$sharePicUrl,$shareSummary);
        }

        if($result){
            return $this->redirect('gamelive/column/list');
        }else{
            return $this->back('保存失败');
        }
    }

    public function getDelete()
    {
        $id = Input::get('id');
        $result = Navigation::RemoveColumn($id);
        if($result==true){
            return $this->back('删除成功');
        }else{
            return $this->back('删除失败');
        }
    }
}