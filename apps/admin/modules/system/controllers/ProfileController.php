<?php
namespace modules\system\controllers;

use Yxd\Modules\System\SettingService;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;



class ProfileController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'system';
	}
	
	public function getIndex()
	{
		$page = Input::get('page',1);
		$pagesize = 15;
		$file = storage_path() . '/logs/profile-apache2handler-' . date('Y-m-d') . '.txt';		
		$table = file($file);
		$total = count($table);
		$data = array();
		foreach($table as $key=>$row){
			$table[$key] = explode(' ',$row);
		}
		rsort($table);
		$datalist = array();
		foreach($table as $key=>$row){
			$item = array();
			$item['id'] = $key+1;
			$item['api_name'] = $row[2];
			$item['exec_time'] = rtrim($row[3],'ms');
			$item['request_time'] = $row[0] . ' ' . $row[1];
			$datalist[] = $item;
		}
		$pager = Paginator::make(array(),$total,$pagesize);
		$data['pagelinks'] = $pager->links();
		chdir(storage_path() . '/logs/');
		$files = glob('profile-apache2handler-*.txt');
		$data['files'] = array_combine($files,$files);
		$pages = array_chunk($datalist,$pagesize,true);		
		$data['datalist'] = isset($pages[$page-1]) ? $pages[$page-1] : array();
		return $this->display('profile-list',$data);
	}	

	public function getSearch()
	{
		
	}
	
	public function getStatic()
	{
		
	}
}