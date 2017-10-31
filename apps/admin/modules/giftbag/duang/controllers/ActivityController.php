<?php
namespace modules\duang\controllers;

use Youxiduo\Activity\Model\DuangGiftbag;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Youxiduo\Helper\Utility;
use Yxd\Modules\Core\BackendController;
use libraries\Helpers;


class ActivityController extends BackendController
{
    public function _initialize()
	{
		$this->current_module = 'duang';
	}
	
	public function getList()
	{
        $data = $search = array();
        $search['title'] = Input::get('title');
        $search['startdate'] = Input::get('startdate');
        $search['enddate'] = Input::get('enddate');
        $search['is_show'] = $cond['is_show'] = Input::get('is_show','all');
        if($search['is_show'] == 'all') unset($search['is_show']);
        $cond['is_shows'] = array( 'all' => '全部' , '1' => '显示' , '0' => '隐藏');

        $sort = (!empty($search['startdate']) || !empty($search['enddate'])) ? array('addtime'=>'desc') : array('id'=>'desc');
		$pageIndex = Input::get('page',1);
		$pageSize = 10;
		$result = DuangGiftbag::search($search,$pageIndex,$pageSize,$sort);

        $pager = Paginator::make(array(),$result['totalCount'],$pageSize);
        $pager->appends($search);
        $data['cond'] = $cond;
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        $data['totalcount'] = $result['totalCount'];
        $data['datalist'] = $result['result'];
		return $this->display('activity-list',$data);
	}
	
	public function getEdit($id=0)
	{
		$data = array();
		if($id){
			$data['activity'] = DuangGiftbag::getInfo($id);
			if($data['activity']) $data['activity']['share_pic'] = Utility::getImageUrl($data['activity']['share_pic']);
		}
		return $this->display('activity-info',$data);
	}
	
    public function postEdit()
	{
		$input['id'] = Input::get('id');
		$input['title'] = Input::get('title');
		$input['is_show'] = (int)Input::get('is_show',0);
		$input['starttime'] = strtotime(Input::get('starttime'));
		$input['endtime'] = strtotime(Input::get('endtime'));
		$input['game_id'] = (int)Input::get('game_id',0);
		$input['game_name'] = Input::get('game_name');
        $input['summary'] = Input::get('summary');
        $input['need_times'] = (int)Input::get('need_times');
        $input['limit_times'] = (int)Input::get('limit_times');
		$input['article_id'] = Input::get('article_id');

		$input['share_title'] = Input::get('share_title');
		$input['share_des'] = Input::get('share_des');
        $file = Input::file('share_pic');
		if($file){
		    $dir = '/u/duang/'.date('Ym').'/';
            $path = Helpers::uploadPic($dir,$file);
		}else{
            $path = '';
		}
		$input['share_pic'] = $path;
        $id = DuangGiftbag::saveInfo($input);
		if($id){
			return $this->redirect('duang/activity/list','活动保存成功');
		}else{
			return $this->back('保存失败');
		}
	}

}