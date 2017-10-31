<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/7/22
 * Time: 17:49
 */

namespace modules\zt_activity\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Youxiduo\Activity\ActivityGiftbagService;
use Youxiduo\Activity\Model\ActivityGiftbag;
use Youxiduo\Helper\Utility;
use Yxd\Modules\Core\BackendController;
use Yxd\Models\Cms\Game as IosGame;
use Youxiduo\Android\Model\Game;


class YxdlibaoController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'zt_activity';
    }

    public function getSearch()
    {
        $data = array();
        $page = Input::get('page',1);
        $tag = Input::get('tag',1);
        $pagesize = 20;

        $result = ActivityGiftbagService::getList($pagesize,$page,true,$tag);
        $pager = Paginator::make(array(),$result['totalCount'],$pagesize);
        $pager->appends(array('tag'=>$tag));
        $data['tag'] = $tag;
        $data['pagelinks'] = $pager->links();
        $data['result'] = $result['result'];
        return $this->display('yxdlibao-list',$data);
    }
    public function getAdd(){
        $data = array();
        return $this->display('yxdlibao-add',$data);
    }

    public function getEdit($id){
        $data['result'] = ActivityGiftbagService::getDetail($id);
        return $this->display('yxdlibao-edit',$data);
    }
    public function postEdit(){
        $input = Input::all();
        $device = isset($input['device']);
        $input['plat'] = $device ? 'android' : 'ios';
        if($device) unset($input['device']);
        unset($input['gname']);
        if($input['plat']=='ios'){
        	$game = IosGame::getGameInfo($input['gid']);        	
        	if($game){
        		$input['gname'] = $game['shortgname'];
        		$input['icon'] = $game['ico'];
        	}        	
        }elseif($input['plat']=='android'){
            $game = Game::db()->where('id','=',$input['gid'])->first();
        	if($game){
        		$input['gname'] = $game['shortgname'];
        		$input['icon'] = $game['ico'];
        	}
        }
        $result = ActivityGiftbag::save($input);
        if($result){
            $url = 'zt_activity/yxdlibao/search';
            $input['tag'] != 1 && $url .= '?tag='.$input['tag'];
            return $this->redirect($url,'修改礼包成功');
        }else{
            return $this->back('修改礼包失败');
        }
    }

    public function postAdd(){
        $input = Input::all();
        $device = isset($input['device']);
        $input['plat'] = $device ? 'android' : 'ios';
        if($device) unset($input['device']);
        unset($input['gname']);
        if($input['plat']=='ios'){
        	$game = IosGame::getGameInfo($input['gid']);
        	if($game){
        		$input['gname'] = $game['shortgname'];
        		$input['icon'] = $game['ico'];
        	}
        }elseif($input['plat']=='android'){
            $game = Game::db()->where('id','=',$input['gid'])->first();
        	if($game){
        		$input['gname'] = $game['shortgname'];
        		$input['icon'] = $game['ico'];
        	}
        }
        $result = ActivityGiftbag::save($input);
        if($result){
            $url = 'zt_activity/yxdlibao/search';
            $input['tag'] != 1 && $url .= '?tag='.$input['tag'];
            return $this->redirect($url,'添加礼包成功');
        }else{
            return $this->back('添加礼包失败');
        }
    }

    public function getDel($id){
        $result = ActivityGiftbag::del($id);
        if($result){
            return $this->redirect('zt_activity/yxdlibao/search','删除礼包成功');
        }else{
            return $this->back('删除礼包失败');
        }
    }

}