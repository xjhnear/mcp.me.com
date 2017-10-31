<?php
namespace modules\system\controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;

class PrivilegeController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'system';
	}
	
	public function getIndex()
	{
		$data = array();
		$data['nodelist'] = Config::get('rule.group_all');
		return $this->display('privilege',$data);
	}
}