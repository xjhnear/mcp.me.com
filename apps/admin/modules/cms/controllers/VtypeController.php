<?php
namespace modules\cms\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;

use Illuminate\Support\Facades\Validator;
use libraries\Helpers;
use Youxiduo\Cms\Model\Videos;
use Youxiduo\Cms\Model\VideosGames;
use modules\game\models\GameModel;
use Youxiduo\Android\Model\Game;
use Youxiduo\Cms\Model\VideosType;
use Yxd\Services\SyncarticleService;
use Illuminate\Support\Facades\Config;


class VtypeController extends BestController{
	public function _initialize(){
		$this->current_module = 'cms';
	}

	/**
	 * 列表页
	 */
	public function getSearch(){
		$page = Input::get('page',1);
		$pagesize = 10;
        $total = VideosType::getListCount();
        $result = VideosType::getList($page,$pagesize);

		$pager = Paginator::make(array(),$total,$pagesize);
		$data['pagination'] = $pager->links();
		$data['list'] = $result;
		return $this->display('vtype-list',$data);
	}
	
	/**
	 * 视频类型添加界面显示
	 */
    public function getAdd(){
		return $this->display('vtype-add');
	}

    public function postAdd(){
        $input = Input::all();
        $rule = array('type_name'=>'required','sort'=>'required','platform'=>'required');
        $prompt = array('type_name.required'=>'类型名称不能为空','sort.required'=>'排序不能为空','platform.required'=>'请选择平台');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }else{
            $data = array(
                'type_name' => $input['type_name'],
                'sort' => $input['sort'],
                'platform' => $input['platform'],
                'is_top' => isset($input['is_top']) ? 1 : 0
            );
            if(VideosType::add($data)){
                return $this->redirect('/cms/vtype/search','添加成功');
            }else{
                return $this->back('添加失败');
            }
        }
    }

    public function getEdit($type_id=''){
        $info = VideosType::getInfo($type_id);
        if(!$info) return $this->back('数据错误');
        return $this->display('vtype-edit',array('info'=>$info));
    }

    public function postEdit(){
        $input = Input::all();
        $rule = array('type_id'=>'required','type_name'=>'required','sort'=>'required','platform'=>'required');
        $prompt = array('type_id.required'=>'数据错误','type_name.required'=>'类型名称不能为空','sort.required'=>'排序不能为空','platform.required'=>'请选择平台');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }else{
            $data = array(
                'type_name' => $input['type_name'],
                'sort' => $input['sort'],
                'platform' => $input['platform'],
                'is_top' => isset($input['is_top']) ? 1 : 0
            );
            if(VideosType::update($input['type_id'],$data)){
                return $this->redirect('/cms/vtype/search','保存成功');
            }else{
                return $this->back('保存失败');
            }
        }
    }

    public function getDel(){
        $type_id = Input::get('type_id',false);
        if(!$type_id) return $this->json(array('state'=>0,'msg'=>'数据错误'));
        if(VideosType::delete($type_id)){
            return $this->json(array('state'=>1,'msg'=>'删除成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'删除失败'));
        }
    }
}