<?php
namespace modules\cms\controllers;

use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;

class ArticleController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'cms';
	}
	
	public function getSearch()
	{
		$data = array();
		
		
		return $this->display('search',$data);
	}
	
    public function getAdd()
	{
		$data = array();
				
		return $this->display('article_info',$data);
	}
	
	public function getEdit()
	{
		$data = array();
				
		return $this->display('article_info',$data);
	}
}