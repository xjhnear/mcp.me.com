<?php
namespace modules\statistics\controllers;

use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use modules\statistics\models\UsercreditModel;


class UsercreditController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'statistics';
	}
	
	public function getIndex()
	{
		$page = Input::get('page',1);
		$pagesize = 10;
		$totalcount = UsercreditModel::getUsersCreditCount();
		$datalist = UsercreditModel::getUsersCredit($page,$pagesize);
		$pager = Paginator::make(array(),$totalcount,$pagesize);
		$data['pagelinks'] = $pager->links();
		$data['datalist'] = $datalist;
		$data['totalcount'] = $totalcount;
		return $this->display('usercredit-list',$data);
	}
}