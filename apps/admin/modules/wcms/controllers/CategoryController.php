<?php
namespace modules\wcms\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;

use modules\wcms\models\Article;
use modules\wcms\models\Picture;
use modules\wcms\models\Video;

class CategoryController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'wcms';
	}
	
	public function getIndex()
	{
		$data = array();
		$catalogs = Article::getArticleAllCategory(false);
		$data['datalist'] = $catalogs;
		return $this->display('category-list',$data);		
	}
	
	public function getAdd()
	{
		return $this->display('category-add');
	}
	
    public function getEdit()
	{
		return $this->display('category-edit');
	}
}