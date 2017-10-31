<?php
namespace modules\adv\controllers;

use Illuminate\Support\Facades\Input;
use Yxd\Modules\Core\BackendController;

class HomeController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'adv';
	}
	
	public function getIndex()
	{
		return $this->redirect('adv/credit/index');
	}
}