<?php
namespace modules\union\controllers;

use Youxiduo\Activity\Duang\CashGameService;
use Youxiduo\Activity\Model\CashGame;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Youxiduo\Game\Model\GamesApk;
use Youxiduo\Helper\Utility;
use Yxd\Modules\Core\BackendController;

class CashgameController extends BackendController
{
    public function _initialize()
	{
		$this->current_module = 'union';
	}
	
	public function getList()
	{
        $data = $search = array();
        $search['type'] = $cond['type'] = Input::get('type','all');
        if($search['type'] == 'all') unset($search['type']);
        $cond['types'] = array( 'all' => '全部' , '1' => '热门' , '2' => '其他');
        $sort = !empty($search['sort']) ? array('sort'=>'desc') : array('id'=>'desc');
		$pageIndex = Input::get('page',1);
		$pageSize = 10;
        $device = Input::get('device',1) == 1 ? 'ios' : 'android';
        $search['device'] = Input::get('device',1);
		$result = CashGameService::getList($search,$pageIndex,$pageSize,$sort,$device);
        $pager = Paginator::make(array(),$result['totalCount'],$pageSize);
        $pager->appends($search);
        $data['cond'] = $cond;
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        $data['totalcount'] = $result['totalCount'];
        $data['datalist'] = $result['result'];
		return $this->display('cash-game-list',$data);
	}
	
	public function getEdit($id=0)
	{
		$data = array();
        $data['cond']['type'] = 1;
        $data['cond']['types'] = array( '1' => '热门' , '2' => '其他');
		if($id){
			$data['cashgame'] = CashGameService::getDetail($id);
            $data['cond']['type'] = $data['cashgame']['type'];
		}
		return $this->display('cash-game-info',$data);
	}

    public function getEditGame($id=0)
    {
        $data = array();
        $data['cond']['type'] = 1;
        $data['cond']['types'] = array( '1' => '热门' , '2' => '其他');
        if($id){
            $cashgame = GamesApk::getGameInfo($id);
            $data['cashgame']['gid'] = $cashgame['id'];
            $data['cashgame']['game_name'] = $cashgame['gname'];
            $data['cashgame']['ico'] = Utility::getImageUrl($cashgame['ico']);
        }
        return $this->display('cash-game-info',$data);
    }
	
    public function postEdit()
	{
		$input['id'] = Input::get('id');
		$input['gid'] = (int)Input::get('gid',0);
		$input['phrase'] = Input::get('phrase');
        $input['sort'] = (int)Input::get('sort',0);
        $input['type'] = Input::get('type');
        $input['discount'] = (double)Input::get('discount',0);
        $input['device'] = Input::get('device') ? 2 : 1;
        $id = CashGame::save($input);

		if($id){
			return $this->redirect('union/cash/list','保存成功');
		}else{
			return $this->back('保存失败');
		}
	}

    public function getDel($id){
        if(!$id) return $this->json(array('state'=>0,'msg'=>'数据错误'));
        if(CashGame::getDel($id)){
            return $this->json(array('state'=>1,'msg'=>'删除成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'删除失败，请刷新页面后重试'));
        }
    }

}