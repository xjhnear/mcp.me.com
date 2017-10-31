<?php
namespace modules\duang\controllers;

use libraries\Helpers;
use Youxiduo\Activity\Model\DuangGiftbag;
use Youxiduo\Activity\Model\DuangPic;
use Yxd\Services\UserService;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\Core\BackendController;

use Youxiduo\Activity\Share\ActivityService;
use Youxiduo\Activity\Share\GiftbagService;
use Youxiduo\Activity\Share\GoodsService;
use Youxiduo\Activity\Share\RechargeService;

class PicController extends BackendController
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
		$result = DuangPic::search($search,$pageIndex,$pageSize,$sort);

        $pager = Paginator::make(array(),$result['totalCount'],$pageSize);
        $pager->appends($search);
        $data['cond'] = $cond;
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        $data['totalcount'] = $result['totalCount'];
        $data['datalist'] = $result['result'];
		return $this->display('pic-list',$data);
	}
	
	public function getEdit($id=0)
	{
		$data = array();
		if($id){
			$data['result'] = DuangPic::getInfo($id);
		}
		return $this->display('pic-info',$data);
	}
	
    public function postEdit()
	{
		$input['id'] = Input::get('id');
		$input['title'] = Input::get('title');
		$input['is_show'] = (int)Input::get('is_show',0);
		$input['url'] = Input::get('url');
        $dir = '/u/duang/' . date('Y') . date('m') . '/';
        $file_img = Input::file('img');
        $img = Helpers::uploadPic($dir, $file_img);
        $input['img'] = $img;
        if(!empty($input['id'])){
            if(!empty($img)){
                $art = DuangPic::saveInfo($input['id']);
                @unlink( storage_path() . $art['img']);
            }
        }else{
            if(empty($img)) return $this->back('请上传图片');
        }
        $id = DuangPic::saveInfo($input);
		if($id){
			return $this->redirect('duang/pic/list','保存成功');
		}else{
			return $this->back('保存失败');
		}
	}

}