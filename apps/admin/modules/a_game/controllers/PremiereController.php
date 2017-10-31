<?php
namespace modules\a_game\controllers;

use Youxiduo\Android\Model\GameFirst;
use Youxiduo\Helper\Utility;
use Youxiduo\V4\Game\GameService;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Yxd\Modules\Core\BackendController;

class PremiereController extends BackendController{
	public function _initialize(){
		$this->current_module = 'a_game';
	}
	
	public function getList(){
		$page = Input::get('page',1);
		$pagesize = 10;
		$title = Input::get('title');
        $platform = 'android';
        $total = GameFirst::getListCount($platform,$title);
        $list = GameFirst::getList($page,$pagesize,$platform,$title);
		$pager = Paginator::make(array(),$total,$pagesize);
		$pager->appends(array('title'=>$title));
		$data['pagination'] = $pager->links();
        $data['list'] = $list;
		
		return $this->display('premiere-list',$data);
	}

    public function getAdd(){
        return $this->display('premiere-add');
    }

    public function postAdd(){
        $input = Input::all();
        $rule = array('title'=>'required','agid'=>'required','state'=>'required','addtime'=>'required');
        $prompt = array('title.required'=>'请填写标识名称','agid.required'=>'请选择游戏','state.required'=>'请填写游戏状态','addtime.required'=>'请选择开测时间');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }else{
            $data = array(
                'title' => $input['title'],
                'state' => $input['state'],
                'agid' => $input['agid'],
                'istop' => isset($input['istop']) ? 1 : 0,
                'isfirst' => isset($input['isfirst']) ? 1 : 0,
                'addtime' => strtotime($input['addtime']),
                'openbeta' => $input['openbeta']
            );
            if(GameFirst::add($data)){
                return $this->redirect('/a_game/premiere/list','添加成功');
            }else{
                return $this->back('添加失败，请重试');
            }
        }
    }

    public function getEdit($id){
        if(!$id) return $this->back('数据错误');
        $info = GameFirst::getInfoById($id);
        $game = array();
        if(isset($info['agid'])){
            $game = GameService::getOneInfoById($info['agid'],'android');
            if($game) $game['ico'] = Utility::getImageUrl($game['ico']);
        }
        return $this->display('premiere-edit',array('info'=>$info,'game'=>$game));
    }

    public function postEdit(){
        $input = Input::all();
        $rule = array('id'=>'required','title'=>'required','agid'=>'required','state'=>'required','addtime'=>'required');
        $prompt = array('id.required'=>'数据错误','title.required'=>'请填写标识名称','agid.required'=>'请选择游戏','state.required'=>'请填写游戏状态','addtime.required'=>'请选择开测时间');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }else{
            $data = array(
                'title' => $input['title'],
                'state' => $input['state'],
                'agid' => $input['agid'],
                'istop' => isset($input['istop']) ? 1 : 0,
                'isfirst' => isset($input['isfirst']) ? 1 : 0,
                'addtime' => strtotime($input['addtime']),
                'openbeta' => $input['openbeta']
            );
            if(GameFirst::update($input['id'],$data)){
                return $this->redirect('/a_game/premiere/list','保存成功');
            }else{
                return $this->back('保存失败，请重试');
            }
        }
    }
}