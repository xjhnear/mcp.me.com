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
use Youxiduo\Zhibo\Model\ZhiboGame;
use Youxiduo\Zhibo\ZhiboService;
use Yxd\Modules\Core\BackendController;



class GameController extends BackendController
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
        $where = $keyword ? array('gname'=>$keyword) : array();
        $result = ZhiboService::getGameList($page,$pagesize,array(),$where);
        $pager = Paginator::make(array(),$result['total'],$pagesize);
        $data['pagelinks'] = $pager->links();
        $data['totalcount'] = $result['total'];
        $data['result'] = $result['result'];
        return $this->display('game-list',$data);
    }
    //添加 | 编辑
    public function getEdit($id = ''){
        if($id){
            $result = ZhiboGame::getDetail($id);
            $data['result'] = $result;
        }else{
            $data = array();
        }
        return $this->display('game-edit',$data);
    }

    //保存数据
    public function postSave(){
        $input = Input::only('id','gname','description');
        $tips = !empty($input['id']) ? '修改' : '添加';
        $rule = array(
            'gname'=>'required',
            'description'=>'required',
        );
        $prompt = array(
            'gname.required'=>'游戏名不能为空',
            'description.required'=>'游戏描述不能为空',
        );
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back($valid->messages()->first());
        }
        $dir = '/userdirs/zhibo/' . date('Y') .'/'. date('m').'/';
        $path = storage_path() . $dir;
        //列表图
        if(Input::hasFile('icon')){
            $file = Input::file('icon');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $input['icon'] = $dir . $new_filename . '.' . $mime;
        }
        $result = ZhiboGame::save($input);
        if($result){
            $tips .= '成功';
        }else{
            $tips .= '失败';
        }
        return $this->redirect('zhibo/game/list',$tips);
    }

    //删除
    public function getDel($id){
        $result = ZhiboGame::getDel($id);
        $tips = '删除';
        if($result){
            $tips .= '成功';
        }else{
            $tips .= '失败';
        }
        return $this->redirect('zhibo/game/list',$tips);
    }



}

