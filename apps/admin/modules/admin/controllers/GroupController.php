<?php
namespace admin\controllers;

use Youxiduo\System\AuthService;
use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;

use Youxiduo\System\Model\AuthGroup;

class GroupController extends BackendController
{	
	public function _initialize()
	{
		$this->current_module = 'admin';
	}
	
	public function getList()
	{
		$data = array();
		$data['datalist'] = AuthGroup::getList(1,20);
		return $this->display('group_list',$data);
	}
	
	public function getAdd()
	{
		$data = array();
		$data['nodelist'] = array();
		$data['admin_all_menu'] = AuthService::getAllMenu();
		return $this->display('group_permission',$data);
	}
	
	public function getEdit($group_id=0)
	{
		$data = array();
		$data['admin_all_menu'] = AuthService::getAllMenu();
		if($group_id){
			$group = AuthGroup::getInfo($group_id);
			if($group){
				$data['group'] = $group;
				$data['nodelist'] = $group['menus_nodes']['nodes'];
			}
		}else{
			$data['nodelist'] = array();
		}
		return $this->display('group_permission',$data);
	}
	
	public function postSaveAuthNode()
	{
		$input = Input::only('group_id','group_name','group_desc');
		$nodes = Input::get('node_id',array());
		$menu = AuthService::getAllMenu();
		$group_menus = array();
		$group_nodes = array();
		$group_nodes = $nodes;
		foreach($menu as $module){
			$data = array(
			    'module_name'=>$module['module_name'],
			    'module_icon'=>isset($module['module_icon'])?$module['module_icon']:'',
			    'module_alias'=>$module['module_alias'],
			    'default_url'=>$module['default_url']
			);
			$data['child_menu'] = array();
			foreach($module['child_menu'] as $node){
				if(in_array($node['url'],$nodes)){
					$data['child_menu'][] = $node;
				}
			}
			if($data['child_menu']){
				$group_menus[$module['module_name']] = $data;
			}
		}
		$input['menus_nodes'] = json_encode(array('menus'=>$group_menus,'nodes'=>$group_nodes));

		$group_id = AuthGroup::saveInfo($input);
		
		return $this->redirect('admin/group/list','组权限修改成功');
	}

	public function getDelete($group_id=0)
	{
		if($group_id){
			AuthGroup::del($group_id);
		}
		return $this->redirect('admin/group/list','组权限删除成功');
	}

}