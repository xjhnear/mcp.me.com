<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/5/8
 * Time: 10:00
 */
namespace modules\union\controllers;

use Youxiduo\Helper\Utility;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Activity\Model\Union\UnionBanner;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use libraries\Helpers;
use Illuminate\Support\Facades\Paginator;

class BannerController extends BackendController{
    public function _initialize(){
        $this->current_module = 'union';
    }

    public function getList(){
        $page = Input::get('page',1);
        $limit = 10;
        $total = UnionBanner::getListCount();
        $list = UnionBanner::getList($page,$limit);
        if($list){
            foreach($list as &$row){
                $row['pic'] = Utility::getImageUrl($row['pic']);
            }
        }
        $pager = Paginator::make(array(),$total,$limit)->links();
        return $this->display('banner-list',array('list'=>$list,'pagination'=>$pager));
    }

    public function getAdd(){
        return $this->display('banner-add');
    }

    public function postAdd(){
        $input = Input::all();

        $rule = array('title'=>'required','summary'=>'required','url'=>'required','pic'=>'required');
        $prompt = array('title.required'=>'请填写标题','summary.required'=>'请填写短描述','url.required'=>'请填写链接','pic.required'=>'请选择图片');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }else{
            $data = array(
                'title'=>$input['title'],
                'is_show'=>isset($input['is_show']) ? 1 : 0,
                'url'=>$input['url'],
                'summary'=>$input['summary'],
                'sort'=>$input['sort'],
                'device'=>isset($input['device']) ? 2 : 1
            );
            if($input['pic']){
                $dir = '/userdirs/union/'.date('Ym').'/';
                $path = Helpers::uploadPic($dir,$input['pic']);
                $data['pic'] = $path;
            }
            if(UnionBanner::insert($data)){
                return $this->redirect('/union/banner/list','添加成功');
            }else{
                return $this->back('添加失败，请重试');
            }
        }
    }

    public function getEdit($banner_id=''){
        if(!$banner_id) return $this->back('数据错误');
        $info = UnionBanner::getInfo($banner_id);
        if($info) $info['pic'] = Utility::getImageUrl($info['pic']);
        return $this->display('banner-edit',array('info'=>$info));
    }

    public function postEdit(){
        $input = Input::all();

        $rule = array('banner_id'=>'required','title'=>'required','summary'=>'required','url'=>'required');
        $prompt = array('banner_id.required'=>'数据错误','title.required'=>'请填写标题','summary.required'=>'请填写短描述','url.required'=>'请填写链接');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }else{
            $data = array(
                'title'=>$input['title'],
                'is_show'=>isset($input['is_show']) ? 1 : 0,
                'url'=>$input['url'],
                'summary'=>$input['summary'],
                'sort'=>$input['sort'],
                'device'=>isset($input['device']) ? 2 : 1
            );
            if($input['pic']){
                $dir = '/userdirs/union/'.date('Ym').'/';
                $path = Helpers::uploadPic($dir,$input['pic']);
                $data['pic'] = $path;
            }
            if(UnionBanner::update($input['banner_id'],$data)){
                return $this->redirect('/union/banner/list','更新成功');
            }else{
                return $this->back('更新失败，请重试');
            }
        }
    }

    public function getDelete(){
        $banner_id = Input::get('banner_id',false);
        if(!$banner_id) return Response::json(array('state'=>0,'msg'=>'数据错误'));
        if(UnionBanner::delete($banner_id)){
            return $this->json(array('state'=>1,'msg'=>'删除成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'删除失败，请刷新页面后重试'));
        }
    }
}