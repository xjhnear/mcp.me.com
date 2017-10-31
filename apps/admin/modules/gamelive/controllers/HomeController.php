<?php
namespace modules\gamelive\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;

use Youxiduo\Helper\Utility;
use modules\gamelive\models\Home;
use modules\gamelive\models\Navigation;
use Youxiduo\Helper\MyHelpLx;
use modules\gamelive\models\Anchor;
use modules\gamelive\models\Video;


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
        $data['idx_options'] = array('1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6');
        $result = Home::GetIndexHeaderVideo();

        foreach($result as $row){
            if($row['idx']==$idx){
                $data['setting'] = $row;
            }
        }
        $data['linkType']= "0";
        if($data['setting']['videoUrl']){
            $data['linkType']= "1";
        }elseif($data['setting']['picAtt']){
            $data['linkType']= "2";
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
        $videoMobileUrl = Input::get('videoMobileUrl');
        $videoLive = Input::get('videoLive');
        $liveMobileUrl = Input::get('liveMobileUrl');
        $selLinkType =Input::get('selLinkType',"0");
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
        if($selLinkType=="0"){
            $videoUrl = "";
            $videoMobileUrl = "";
            $picAtt = "";
        }elseif($selLinkType=="1"){
            $picAtt = "";
            $videoLive = "";

        }elseif($selLinkType=="2"){
            $videoUrl = "";
            $videoMobileUrl = "";
            $videoLive = "";
        }
        $result = Home::SaveIndexHeaderVideo($idx,$picUrl,$summary,$videoUrl,$picAtt,$videoLive,$picMin,$videoMobileUrl,$liveMobileUrl);
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
        $week = array();
        $data = array();
        if($day){
            $result = Home::GetIndexWeekShow();
            foreach($result as $row){
                if($row['idx'] == $idx){
                    $week = $row;
                }
            }
        }
        if($day){
            $week['day'] = $day;
            $week['idx'] = $idx;
        }
        if(isset($week['result'])){
            foreach($week['result'] as &$item){
                if(isset($item['peoples'])){
                    $item['peoples'] = implode(',', $item['peoples']);
                    if($item['peoples']){
                        $peopleNames = Anchor::GetPeopleNames($item['peoples']);
                        if($peopleNames){
                            $item['peopleNames'] = $peopleNames;
                        }
                    }
                }
                if(isset($item['videoId'])&&$item['videoId']){
                    $video = Video::GetVideoDetail($item['videoId']);
                    if(isset($video['title'])){
                        $item['videoName'] = $video['title'];
                    }
                }
                if(isset($item['starttime'])){
                    $item['starttime'] = date('Y-m-d H:i:s',(int)$item['starttime']);
                }
                if(isset($item['endtime'])){
                    $item['endtime'] = date('Y-m-d H:i:s',(int)$item['endtime']);
                }
            }
        }
        $data['columns'] = Navigation::GetColumnOptions();
        $data['data'] = isset($week['result'][0]) ? $week['result'][0] : array();
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
        if($day){
            $success = Home::SaveIndexWeekShow($day,$result,$idx);
        }else{
            $success = Home::CreateIndexWeekShow(array('result'=>json_encode($result)));
        }
        if($success){
            return $this->redirect('gamelive/home/week-list');
        }else{
            return $this->back('保存失败');
        }
    }
    public function getIndexDown(){
        $data = array();
        $search = array();
        $list = Home::GetIndexDown($search);
        $data['datalist'] = $list['data'];
        return $this->display('index-down-list',$data);
    }
    public function getIndexDownAdd(){
        $data = array();
        $id = Input::get('id',"");
        if($id!=""){
            $list = Home::GetIndexDown(array());
            foreach($list['data'] as $k=>$v){
                if($v['idx'] == $id){
                    $data['data'] = $v;
                }
            }
        }
        return $this->display('index-down-add',$data);
    }
    public function postSaveIndexDown(){
        $input = Input::all();
        $img1 = MyHelpLx::save_img($input['picUrl']);
        $input['picUrl'] =$img1 ? $img1:$input['img'];unset($input['img']);
        $res = Home::SaveIndexDown($input);
        if($res['success']){
            return $this->redirect('gamelive/home/index-down');
        }
        return $this->back($res['error']);
    }
    public function postRemoveIndexDown(){
        $id = Input::get('id',"");
        $res = Home::RemoveIndexDown(array('idx'=>$id));
        echo json_encode($res);
    }

    public function getIndexGgCs(){
        $data = array();
        $search = array();
        $list = Home::GetIndexGuangGaoCS($search);
        $data['datalist'] = $list['data'];
        return $this->display('index-gg-cs-list',$data);
    }
    public function getIndexGgCsAdd(){
        $data = array();
        $id = Input::get('id',"");
        if($id!=""){
            $list = Home::GetIndexGuangGaoCS(array());
            foreach($list['data'] as $k=>$v){
                if($v['idx'] == $id){
                    $data['data'] = $v;
                }
            }
        }
        return $this->display('index-gg-cs-add',$data);
    }
    public function postSaveIndexGgCs(){
        $input = Input::all();
        $img1 = MyHelpLx::save_img($input['picUrl']);
        $input['picUrl'] =$img1 ? $img1:$input['img'];unset($input['img']);
        $res = Home::SaveIndexGuangGaoCS($input);
        if($res['success']){
            return $this->redirect('gamelive/home/index-gg-cs');
        }
        return $this->back($res['error']);
    }
    public function postRemoveIndexGgCs(){
        $id = Input::get('id',"");
        $res = Home::RemoveIndexCS(array('idx'=>$id));
        echo json_encode($res);
    }

    public function getIndexGgFooter(){
        $data = array();
        $search = array();
        $list = Home::GetIndexGuangGaoFooter($search);
        if($list['success']&&$list['data']){
            $data['datalist'] = json_decode($list['data'][0]['content'],true);
        }
        return $this->display('index-footer-list',$data);
    }
    public function getIndexGgFooterAdd(){
        $data = array();
        $id = Input::get('id',"");
        if($id){
            $id--;
            $list = Home::GetIndexGuangGaoFooter(array());
            $data['data'] = $list['data'][$id];
            $data['data']['content'] = json_decode($data['data']['content'],true);
        }
        return $this->display('index-footer-add',$data);
    }
    public function postSaveIndexGgFooter(){
        $input = Input::all();
        $game = array();
        $name = $input['gameName'];unset($input['gameName']);
        $url = $input['gameUrl'];unset($input['gameUrl']);
        foreach($name as $k=>$v){
            $game[] = array('name'=>$v,'url'=>$url[$k]);
        }
        $input['content'] = json_encode($game);
        $input['content'] = substr($input['content'],1,-1);
        $res = Home::SaveIndexGuangGaoFooter($input);
        if($res['success']){
            return $this->redirect('gamelive/home/index-gg-footer');
        }
        return $this->back($res['error']);
    }
    public function postRemoveIndexGgFooter(){
        $id = Input::get('id',"");
        $res = Home::RemoveIndexGuangGaoFooter(array('idx'=>$id));
        echo json_encode($res);
    }

    public function getHotGame(){
        $data = array();
        $search = array();
        $list = Home::GetVideoHotGame($search);
//        print_r($list);
        if($list['success']&&$list['data']){
            $data['datalist'] = $list['data'];
        }
        return $this->display('hot-game-list',$data);
    }
    public function getHotGameAdd(){
        $data = array();
        $id = Input::get('id',"");
        if($id){
            $list = Home::GetVideoHotGame(array());
            foreach($list['data'] as $k=>$v){
                if($v['idx'] == $id){
                    $data['data'] = $v;
                }
            }
        }
//        print_r($data);
        return $this->display('hot-game-add',$data);
    }
    public function postSaveHotGame(){
        $input = Input::all();
        $res = Home::SaveVideoHotGame($input);
//        unset($input['id']);
//        print_r($input);print_r($res);die;
        if($res['success']){
            return $this->redirect('gamelive/home/hot-game');
        }
        return $this->back($res['error']);
    }
    public function postRemoveHotGame(){
        $id = Input::get('id',"");
        $res = Home::RemoveVideoHotGame(array('idx'=>$id));
        echo json_encode($res);
    }



    public function postAjaxUploadImg()
    {
        if(Input::file('filedata')){
            $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
            $path = storage_path() . $dir;
            $file_arr = Input::file('filedata');
            $file = $file_arr[0];
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $icon = $dir . $new_filename . '.' . $mime;
            $icon = Utility::getImageUrl($icon);
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>$icon));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>'修改失败','data'=>"图片丢失"));
        }
    }

    public function postAjaxWeekSave(){
        $data = array();
        $input = Input::all();
        $data['day'] = Input::get('day');unset($input['day']);
        $data['idx'] = Input::get('idx');unset($input['idx']);
        $data['uid'] = Input::get('uid');unset($input['uid']);
        if($input['peoples']){
            $input['peoples'] = explode(',',$input['peoples']);
        }else{
            $input['peoples'] = array();
        }
        $input['starttime'] = strtotime($input['starttime']);
        $input['endtime'] = strtotime($input['endtime']);
        $data['result'] = json_encode($input,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        if( $data['uid']){
            $res = Home::UpdateIndexWeekShow($data);
        }else{
            unset($data['uid']);
            $res = Home::InsertIndexWeekShow($data);
        }
//        print_r($input);print_r($data);print_r($res);die;
        echo json_encode($res);
    }

    public function postAjaxWeekDel(){
        $input = Input::all();
        $res = Home::RemoveIndexWeekShow($input);
        echo json_encode($res);
    }
}