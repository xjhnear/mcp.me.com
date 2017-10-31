<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/5/14
 * Time: 14:20
 */
namespace modules\zhibo\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Youxiduo\Zhibo\Model\ZhiboGuest;
use Youxiduo\Zhibo\ZhiboService;
use Yxd\Modules\Core\BackendController;



class GuestController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'zhibo';
    }

    //列表
    public function getList(){

        $data = array();
        $page = Input::get('page',1);
        $keyword = Input::get('keyword','');
        $pagesize = 20;
        $where = $keyword ? array('name'=>$keyword) : array();
        $result = ZhiboService::getGuestList($page,$pagesize,array(),$where);
        $pager = Paginator::make(array(),$result['total'],$pagesize);
        $data['pagelinks'] = $pager->links();
        $data['totalcount'] = $result['total'];
        $data['result'] = $result['result'];
        return $this->display('guest-list',$data);
    }

    //添加 | 编辑
    public function getEdit($id = ''){
        if($id){
            $result = ZhiboGuest::getDetail($id);
            $data['result'] = $result;
        }else{
            $data = array();
        }
        return $this->display('guest-edit',$data);
    }


    //保存数据
    public function postSave(){
        $input = Input::only('id','name','introduction');
        $tips = !empty($input['id']) ? '修改' : '添加';
        $rule = array(
            'name'=>'required',
            'introduction'=>'required',
        );
        $prompt = array(
            'name.required'=>'游戏名不能为空',
            'introduction.required'=>'游戏描述不能为空',
        );
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back($valid->messages()->first());
        }
        $dir = '/userdirs/zhibo/' . date('Y') .'/'. date('m').'/';
        $path = storage_path() . $dir;
        //列表图
        if(Input::hasFile('webpic')){
            $file = Input::file('webpic');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $input['webpic'] = $dir . $new_filename . '.' . $mime;
        }
        if(Input::hasFile('h5pic')){
            $file = Input::file('h5pic');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $input['h5pic'] = $dir . $new_filename . '.' . $mime;
        }
        $result = ZhiboGuest::save($input);
        if($result){
            $tips .= '成功';
        }else{
            $tips .= '失败';
        }
        return $this->redirect('zhibo/guest/list',$tips);
    }

    public function getShow($id,$is_show){
        $data['id'] = $id;
        $data['is_show'] = $is_show ? 0 : 1;
        $result = ZhiboGuest::save($data);
        if($result){
            $tips = '修改成功';
        }else{
            $tips = '修改失败';
        }
        return $this->redirect('zhibo/guest/list',$tips);
    }

    //删除
    public function getDel($id){
        $result = ZhiboGuest::getDel($id);
        $tips = '删除';
        if($result){
            $tips .= '成功';
        }else{
            $tips .= '失败';
        }
        return $this->redirect('zhibo/guest/list',$tips);
    }
}

