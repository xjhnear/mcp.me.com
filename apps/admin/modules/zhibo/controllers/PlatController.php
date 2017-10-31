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
use Youxiduo\Zhibo\Model\ZhiboPlat;
use Youxiduo\Zhibo\ZhiboService;
use Yxd\Modules\Core\BackendController;



class PlatController extends BackendController
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
        $result = ZhiboService::getPlatList($page,$pagesize,array(),$where);
        $pager = Paginator::make(array(),$result['total'],$pagesize);
        $data['pagelinks'] = $pager->links();
        $data['totalcount'] = $result['total'];
        $data['result'] = $result['result'];
        return $this->display('plat-list',$data);
    }

    //添加 | 编辑
    public function getEdit($id = ''){
        if($id){
            $result = ZhiboPlat::getDetail($id);
            $data['result'] = $result;
        }else{
            $data = array();
        }
        return $this->display('plat-edit',$data);
    }


    //保存数据
    public function postSave(){
        $input = Input::only('id','title','url');
        $tips = !empty($input['id']) ? '修改' : '添加';

        $rule = array(
            'title'=>'required',
            'url'=>'required',
//            'icon'=>'image',
//            'icon_hover'=>'image',
//            'h5_icon'=>'image'
        );
        $prompt = array(
            'title.required'=>'游戏名不能为空',
            'url.required'=>'链接不能为空',
            'url.active_url'=>'链接格式不正确',
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
        if(Input::hasFile('icon_hover')){
            $file = Input::file('icon_hover');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $input['icon_hover'] = $dir . $new_filename . '.' . $mime;
        }
        if(Input::hasFile('h5_icon')){
            $file = Input::file('h5_icon');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $input['h5_icon'] = $dir . $new_filename . '.' . $mime;
        }
        $result = ZhiboPlat::save($input);
        if($result){
            $tips .= '成功';
        }else{
            $tips .= '失败';
        }
        return $this->redirect('zhibo/plat/list',$tips);
    }

    //删除
    public function getDel($id){
        $result = ZhiboPlat::getDel($id);
        $tips = '删除';
        if($result){
            $tips .= '成功';
        }else{
            $tips .= '失败';
        }
        return $this->redirect('zhibo/plat/list',$tips);
    }
}

