<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/3
 * Time: 13:57
 */

namespace modules\zt_activity\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\Utility;

use modules\zt_activity\models\Hd;

class HdController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'zt_activity';
    }

    public function getSearch()
    {
        $pageIndex = Input::get('page',1);
        $pageSize = 10;
        $data = array();
        $result = Hd::search($pageIndex,$pageSize);
        $data['datalist'] = $result['activitys'];
        $pager = Paginator::make(array(),$result['total'],$pageSize);
        $data['pagelinks'] = $pager->links();

        return $this->display('hd-list',$data);
    }

    public function getEdit($id=null)
    {
        $id = Input::get('id',$id);
        $data = array();
        if($id){
            $data['result'] = Hd::info($id);
            !isset($data['result']['prizes']) && $data['result']['prizes'] = '';
            !isset($data['result']['pics']) && $data['result']['pics'] = '';
            $data['prizes'] = json_encode(explode(';',$data['result']['prizes']));
            $data['pics'] = json_encode(explode(';',$data['result']['pics']));

        }else{
            $data['prizes'] = json_encode(null);
            $data['pics'] = json_encode(null);
        }

        return $this->display('hd-edit',$data);
    }

    public function postEdit()
    {
        $id = Input::get('id');
        $pagetitle = Input::get('pagetitle','');
        $pagedate = Input::get('pagedate','');
        $pagebg = Input::get('pagebg','');
        $gameicon = Input::get('gameicon','');
        $videopic = Input::get('videopic','');
        $gamedesc = Input::get('gamedesc','');
        $iosdown = Input::get('iosdown','');
        $anddown = Input::get('anddown','');
        $video = Input::get('video','');
        $activitydesc = Input::get('activitydesc','');
        $partake = Input::get('partake','');
        $ofurl = Input::get('ofurl');
        $zqurl = Input::get('zqurl');


        $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
        $path = storage_path() . $dir;
        //����ͼ
        if(Input::hasFile('file_pagebg')){

            $file = Input::file('file_pagebg');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $pagebg = $dir . $new_filename . '.' . $mime;
            $pagebg = Utility::getImageUrl($pagebg);
        }

        //��ϷICON
        if(Input::hasFile('file_gameicon')){

            $file = Input::file('file_gameicon');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $gameicon = $dir . $new_filename . '.' . $mime;
            $gameicon = Utility::getImageUrl($gameicon);
        }

        //��Ƶͼ
        if(Input::hasFile('file_videopic')){

            $file = Input::file('file_videopic');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $videopic = $dir . $new_filename . '.' . $mime;
            $videopic = Utility::getImageUrl($videopic);
        }

        $img_name_prizes = Input::get('img_name_prize');
        if($img_name_prizes && is_array($img_name_prizes)){

            foreach($img_name_prizes as $key=>$pic){
                $prizes[] = $pic;
            }
            $prizes = implode(';',$prizes);
        }

        $img_names = Input::get('img_name');
        if($img_names && is_array($img_names)){

            foreach($img_names as $key=>$pic){
                $pics[] = $pic;
            }
            $pics = implode(';',$pics);
        }
       // print_r($prizes);exit;
        $result = false;
        if($id){
            $result = Hd::update($id,$pagetitle,$pagedate,$pagebg,$gameicon,$gamedesc,$iosdown,$anddown,$video,$activitydesc,$prizes,$partake,$pics,$videopic,$ofurl,$zqurl);
        }else{
            $result = Hd::add($pagetitle,$pagedate,$pagebg,$gameicon,$gamedesc,$iosdown,$anddown,$video,$activitydesc,$prizes,$partake,$pics,$videopic,$ofurl,$zqurl);
        }
        if($result){
            return $this->redirect('zt_activity/hd/search');
        }
        return $this->back('����ʧ��');
    }

    public function getDelete($id)
    {
        Hd::delete($id);
        return $this->back();
    }

}