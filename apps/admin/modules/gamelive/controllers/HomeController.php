<?php
namespace modules\gamelive\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;

use Youxiduo\Helper\Utility;
use modules\gamelive\models\Home;
use modules\gamelive\models\Navigation;


class HomeController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'gamelive';
    }

    public function getSlideList()
    {
        $data = array();
        $data['datalist'] = Home::GetIndexHeaderVideo();
        return $this->display('home-slide-list',$data);
    }

    public function getSlideEdit()
    {
        $data = array();
        $idx = Input::get('idx');
        $data['idx_options'] = array('1'=>'1','2'=>'2','3'=>'3');
        if($idx){
            $result = Home::GetIndexHeaderVideo();
            foreach($result as $row){
                if($row['idx']==$idx){
                    $data['setting'] = $row;
                }
            }
        }
        return $this->display('home-slide-edit',$data);
    }

    public function postSlideEdit()
    {
        $idx = Input::get('idx');
        $picUrl = Input::get('picUrl');
        $picMin = Input::get('picMin');
        $summary = Input::get('summary');
        $videoUrl = Input::get('videoUrl');
        $videoLive = Input::get('videoLive');
        $picAtt = Input::get('picAtt');
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
        if(Input::hasFile('filemin')){

            $file = Input::file('filemin');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $picMin = $dir . $new_filename . '.' . $mime;
            $picMin = Utility::getImageUrl($picMin);
        }


        $result = Home::SaveIndexHeaderVideo($idx,$picUrl,$summary,$videoUrl,$picAtt,$videoLive,$picMin);
        if($result==true){
            return $this->redirect('gamelive/home/slide-list','设置保存成功');
        }else{
            return $this->back('设置保存失败');
        }
    }

    public function getSlideDelete($_idx=0)
    {
        $idx = Input::get('idx',$_idx);
        $result = Home::RemoveIndexHeaderVideo($idx);
        if($result){
            return $this->back('删除成功');
        }
        return $this->back('删除失败');
    }

    public function getWeekList()
    {
        $data = array();
        $result = Home::GetIndexWeekShow();
        $data['datalist'] = $result;
        return $this->display('home-week-list',$data);
    }

    public function getWeekEdit()
    {
        $day = Input::get('day');
        $idx = Input::get('idx');
        $result = Home::GetIndexWeekShow();
        $week = array();
        foreach($result as $row){
            if($row['idx'] == $idx){
                $week = $row;
            }
        }
        $data = array();
        if($day){
            $week['day'] = $day;
            $week['idx'] = $idx;
        }

        $data['columns'] = Navigation::GetColumnOptions();
        $data['one'] = isset($week['result'][0]) ? $week['result'][0] : array();
        $data['two'] = isset($week['result'][1]) ? $week['result'][1] : array();
        $data['week'] = $week;
        return $this->display('home-week-edit',$data);
    }

    public function postWeekEdit()
    {
        $day = Input::get('day');
        $idx = Input::get('idx');
        $title = Input::get('title');
        $summary = Input::get('summary');
        $picUrl = Input::get('picUrl');
        $tag = Input::get('tag');
        $url = Input::get('url');
        $starttime = Input::get('starttime');
        $endtime = Input::get('endtime');
        $videoId = Input::get('videoId');
        $columnId = Input::get('columnId');
        $result = array();

        $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
        $path = storage_path() . $dir;
        //列表图

        $file = Input::file('filedata');
        if($file[0]){
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file[0]->getClientOriginalExtension();
            $file[0]->move($path,$new_filename . '.' . $mime );
            $picUrl[0] = $dir . $new_filename . '.' . $mime;
            $picUrl[0] = Utility::getImageUrl($picUrl[0]);
        }
        if($file[1]){
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file[1]->getClientOriginalExtension();
            $file[1]->move($path,$new_filename . '.' . $mime );
            $picUrl[1] = $dir . $new_filename . '.' . $mime;
            $picUrl[1] = Utility::getImageUrl($picUrl[1]);
        }

        if($title[0]){
            $result[] = array(
                'title'=>$title[0],
                'summary'=>$summary[0],
                'picUrl'=>$picUrl[0],
                'tag'=>explode(',',$tag[0]),
                'url'=>$url[0],
                'starttime'=>strtotime($starttime[0]),
                'endtime'=>strtotime($endtime[0]),
                'columnId'=>$columnId[0],
                'videoId'=>$videoId[0]
            );
        }
        if($title[1]){
            $result[] = array(
                'title'=>$title[1],
                'summary'=>$summary[1],
                'picUrl'=>$picUrl[1],
                'tag'=>explode(',',$tag[1]),
                'url'=>$url[1],
                'starttime'=>strtotime($starttime[1]),
                'endtime'=>strtotime($endtime[1]),
                'columnId'=>$columnId[1],
                'videoId'=>$videoId[1]
            );
        }

        $success = Home::SaveIndexWeekShow($day,$result,$idx);
        if($success){
            return $this->redirect('gamelive/home/week-list');
        }else{
            return $this->back('保存失败');
        }
    }
}