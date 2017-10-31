<?php
namespace modules\adv\controllers;

use modules\adv\models\AdvModel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Yxd\Modules\Core\BackendController;

class AdsController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'adv';
	}
	
	public function getList($type)
	{
		$search = array('type'=>$type,'version'=>'3.0.0');
		$advtype = Config::get('yxd.advtype');
		$data = array();
		$data['advtype'] = $advtype[$type];
		$data['type'] = $type;
		$data['imgurl'] = Config::get('app.img_url');
		$data['datalist'] = AdvModel::search($search);
		return $this->display('ads-list',$data);
	}	
	
	public function getCreate($type)
	{
		$advtype = Config::get('yxd.advtype');
		$applist = Config::get('yxd.applist');
		$data = array();
		$data['applist'] = $applist;
		$data['versionlist'] = Config::get('yxd.app_version');
		$data['location'] = $this->getLocation($type);
		$data['advtype'] = $advtype[$type];
		$data['type'] = $type;
		return $this->display('ads-edit',$data);
	}
	
	public function getEdit($id)
	{
		$advtype = Config::get('yxd.advtype');
		$applist = Config::get('yxd.applist');
		$data = array();
		$adv = AdvModel::getInfo($id);
		$type = $adv['type'];
		$data['applist'] = $applist;		
		$data['versionlist'] = Config::get('yxd.app_version');
		$data['location'] = $this->getLocation($type);
		$data['advtype'] = $advtype[$type];
		$data['type'] = $type;
		$data['adv'] = $adv;
		return $this->display('ads-edit',$data);
	}
	
	public function postSave()
	{
		return $this->back()->with('global_tips','功能开发中,敬请期待');
	}
	
	/**
	 * 
	 */
	protected function getLocation($type)
	{
		$advtype = Config::get('yxd.advtype');
		$typename = $advtype[$type];
		$list = array();
		$type = (int)$type;
		switch($type){
			case 1:
				for($i=1;$i<=5;$i++){
					$list[$i] = $typename . $i;
				}
				break;
			case 2:
		        for($i=11;$i<=26;$i++){
					$list[$i] = $typename . $i;
				}
				break;
			case 3:
				$list = array('21'=>$typename . '21');
				break;
			case 4:
				$list = array('23'=>$typename . '23');
				break;
			case 5:
				$list = array('22'=>$typename . '22');
				break;
			case 6:
				$list = array('24'=>$typename . '24');
				break;
			case 7:
				$list = array('26'=>$typename . '26');
				break;
			case 8:
				$list = array('25'=>$typename . '25');
				break;						
		}
		
		return $list;
	}
}