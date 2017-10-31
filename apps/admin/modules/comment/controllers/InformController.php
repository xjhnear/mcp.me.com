<?php
namespace modules\comment\controllers;
use Yxd\Models\InformModel;

use Yxd\Services\UserService;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;
use modules\comment\models\CommentModel;

class InformController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'comment';
	}
	
	public function getList()
	{
		$page = Input::get('page',1);
		$pagesize = 10;
		$result = InformModel::getList($page,$pagesize,2);	
		$totalcount = $result['total'];
		$data = array();
		$data['datalist'] = $result['list'];
		$pager = Paginator::make(array(),$totalcount,$pagesize);
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $totalcount;	
		return $this->display('inform-list',$data);
	}
	
    public function getDo($id)
	{
		InformModel::doDelete($id);
		return $this->back()->with('global_tipic','处理完成');
	}
	
	public function getIgnore($id)
	{
		InformModel::doIgnore($id);
		return $this->back()->with('global_tipic','处理完成');
	}
}