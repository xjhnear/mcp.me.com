<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/8/10
 * Time: 11:53
 */
namespace modules\other\controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use libraries\Helpers;
use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Input;
use Youxiduo\Other\DiscoveryService;
use Youxiduo\V4\Game\GameService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Paginator;


class DiscoveryController extends BackendController{
    const GENRE = 1;
    const GENRE_STR = 'core';

    public function _initialize(){
        $this->current_module = 'other';
    }

    public function getList(){


        $vdata = array('list'=>array(),'totalcount'=>0);
//        $name = Input::get('name','');
//        $page = Input::get('page',1);
//
//        $size = 15;
//        if(Input::get('name')) $params['name'] =Input::get('name');

        $res_list = DiscoveryService::get_discovery_list();
        foreach ($res_list['result'] as &$item){
            $item['pic'] = Utility::getImageUrl($item['pic']);
        }
        if(!$res_list['errorCode'] && $res_list['result']) $vdata['list'] = $res_list['result'];
//        $res_num = TopicService::getForumsCount($name);
//        if(!$topic_num_res['errorCode']) $vdata['totalcount'] = $topic_num_res['totalCount'];

//        $search = array('name'=>$name);
//      $pager = Paginator::make(array(),$vdata['totalcount'],$size);
//		$pager->appends($search);
//		$vdata['search'] = $search;
//		$vdata['pagelinks'] = $pager->links();

        return $this->display('/discovery/discovery-list',$vdata);
    }


    public function getSave(){
        $data = array(
            'linkType'=>array(
                '1'   =>'外部url',
                '2'   =>'内部url',
                '1044'=>'游戏视频',
                '1045'=>'游戏论坛',
                '1046'=>'新游预告',
                '1047'=>'手游新闻',
                '1048'=>'经典必玩',
                '1048'=>'经典必玩2',
                '1049'=>'特色专题'
            ),
            'subType'=>3
        );
        return $this->display('/discovery/discovery-add',$data);
    }

    public function getEdit($id="",$dataSubType=0){
        if(!$id) return $this->back('数据错误');
        $info_res = DiscoveryService::get_discovery_list(1,$id);
        if($info_res['errorCode'] || !$info_res['result']) return $this->back('数据不存在');
        $data = array(
            'linkType'=>array(
                '1'   =>'外部url',
                '2'   =>'内部url',
                '1044'=>'游戏视频',
                '1045'=>'游戏论坛',
                '1046'=>'新游预告',
                '1047'=>'手游新闻',
                '1048'=>'经典必玩',
                '1049'=>'特色专题'
            ),
            'dis'=>$info_res['result'][0],
            'subType'=>$dataSubType
        );
//        print_r($info_res['result'][0]);
        //var_dump($data);die;
        return $this->display('/discovery/discovery-add',$data);
    }

    public function postSave(){
        $input = Input::all();
        $rule = array('title'=>'required','description'=>'required','top'=>'required','pic'=>'image','linkValue'=>'required');
        $prompt = array('title.required'=>'请填写标题','description.required'=>'请填写介绍','pic.image'=>'请选择图片','top.required'=>'请填写排序','linkValue.required'=>'请填写url');
        if($input['subType']!=3){
            unset($rule['top']);
            unset($rule['linkValue']);
            unset($prompt['top.required']);
            unset($prompt['linkValue.required']);
        }

        $path ="";
        if(Input::hasFile('pic')){
            $dir = '/userdirs/discovery/pic/';
            $path = Helpers::uploadPic($dir,$input['pic']);
        }else{
            if($input['id']){
                $dis1 = DiscoveryService::get_discovery_list(1,$input['id']);
                $path = $dis1['result'][0]['pic'];
            }

        }
        if(!$path){
            return $this->back()->withInput()->with('global_tips',"请选择图片");
        }
        $data = array();
        $input['id'] && $data['id']=$input['id'];
        $data['title'] =$input['title'];
        $data['description'] =$input['description'];
        $data['top'] =$input['top'];
        $data['linkType'] =$input['linkType'];
        $data['linkValue'] =$input['linkValue'];
        $data['pic'] =$path;
        if($data['linkType']!="1"&&$data['linkType']!="2"){
            $data['linkId'] = $data['linkType'];
            $data['linkType'] = "0";
        }else{
            $data['linkId'] = "";
        }
        //是否自动登录
        if($input['linkType'] == 1 || $input['linkType'] == 2){
            if($input['isAutoLogin'] == 1){
                $data['isAutoLogin'] = "true";
            }else{
                $data['isAutoLogin'] = "false";
            }
        }
        //var_dump($data);die;
        if($input['id']){
            $res = DiscoveryService::edit_discovery($data);
        }else{
            unset($data['id']);
            $res = DiscoveryService::add_discovery($data);
        }


        if($res['errorCode']==0&&$res['result']){
            return $this->redirect('other/discovery/list','保存成功');
        }else{
            return $this->back()->withInput()->with('global_tips',$res['errorDescription']);
        }


    }

    public function postDel(){
        $id = input::get('id');
        if($id){
            $res = DiscoveryService::del_discovery($id);
//          print_r($res);
            if(!$res['errorCode']&&$res['result']){
                echo json_encode(array('success'=>true,'mess'=>'删除成功','data'=>'删除成功'));
            }else{
                echo json_encode(array('success'=>false,'mess'=>'删除失败','data'=>$res['errorDescription']));
            }
        }else{
            echo json_encode(array('success'=>false,'mess'=>'缺少参数','data'=>'缺少参数'));
        }
    }



    public function postEdit(){
        $input = Input::all();
        $rule = array('fid'=>'required','forum_name'=>'required','gid'=>'required');
        $prompt = array('fid.required'=>'数据错误','forum_name.required'=>'请填写论坛名称','gid.required'=>'请选择游戏');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        $game_info = GameService::getOneInfoById($input['gid'],self::GENRE_STR);
        $path = $game_info['ico'];
        if(Input::hasFile('top_banner')){
            $dir = '/userdirs/forum/top_banner/';
            $path = Helpers::uploadPic($dir,$input['top_banner']);
        }
        $add_res = TopicService::updateForum($input['fid'],$input['forum_name'],$path); //改成编辑，暂未提供
        if($add_res['errorCode'] || !$add_res['result']) return $this->back()->withInput()->with('global_tips','添加失败');
        $rel_res = TopicService::saveForumAndGameRelation($add_res['result'],$input['gid'],self::GENRE);
        if($add_res['errorCode']==0){
            return $this->redirect('web_forum/forum/forum-list','修改成功');
        }else{
            return $this->back()->withInput()->with('global_tips','修改失败');
        }
    }

    public function getGameColumnList(){

        $res_list = DiscoveryService::get_discovery_list(2);
        foreach($res_list['result'] as &$v ){
            if(isset($v['pic'])){
                $v['pic'] = json_decode($v['pic'],true);
            }
        }
        return $this->display('/discovery/game-column-list',array('list'=>$res_list['result']));
    }

    public function getColumnEdit($id="",$dataSubType=2){
        if(!$id) return $this->back('数据错误');
        $info_res = DiscoveryService::get_discovery_list($dataSubType,$id);
        if(isset($info_res['result'][0]['pic'])){
            $info_res['result'][0]['pic'] = json_decode($info_res['result'][0]['pic'],true);
        }
        if($info_res['errorCode'] || !$info_res['result']) return $this->back('数据不存在');
        return $this->display('/discovery/column-edit',array('dis'=>$info_res['result'][0],'subType'=>$dataSubType));
    }

    public function postColumnEdit(){
        $input = Input::all();
        $rule = array('title'=>'required','pic'=>'image');
        $prompt = array('title.required'=>'请填写标题','pic.image'=>'请选择图片');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());

        $path ="";$path2 ="";
        if(Input::hasFile('pic')){
            $dir = '/userdirs/discovery/pic/';
            $path = Helpers::uploadPic($dir,$input['pic']);
        }else{
            $path = Input::get("img");
        }

        if(Input::hasFile('pic2')){
            $dir = '/userdirs/discovery/pic/';
            $path2 = Helpers::uploadPic($dir,$input['pic2']);
        }else{
            $path2 = Input::get("img2");
        }
        if(!$path){
            return $this->back()->withInput()->with('global_tips',"请选择图片");
        }
        $data = array();
        $input['id'] && $data['id']=$input['id'];
        $data['title'] = $input['title'];
        $data['pic'] = json_encode(array('grayPic'=>$path,'brightPic'=>$path2));
        $data['dataType'] = 2;
        if($input['id']){
            $res = DiscoveryService::edit_discovery($data);
        }
//        print_r($data);
//        print_r($res);die;
        if($res['errorCode']==0&&$res['result']){
            return $this->redirect('other/discovery/game-column-list','保存成功');
        }else{
            return $this->back()->withInput()->with('global_tips','保存失败');
        }
    }


}
