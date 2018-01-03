<?php
namespace admin\controllers;

use Yxd\Modules\Core\BackendController;
use Youxiduo\System\AuthService;
use Youxiduo\System\Model\Admin;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;

use Youxiduo\System\Model\AuthGroup;
use Youxiduo\System\Model\Module;

class AdminController extends BackendController
{
	public function _initialize(){
		$this->current_module = 'admin';
	}
	
	public function getList()
	{
		if($this->current_user['group_id']!=1) return $this->groupManage();
		$pageIndex = Input::get('page',1);
		$search = Input::only('group_id','username','showall');		
		$pageSize = 10;
		$data = array();
		$data['datalist'] = Admin::getList($search,$pageIndex,$pageSize);
		$groups = AuthGroup::getNameList();
		$groups[0] = '无';
		$data['groups'] = $groups;
		$data['search'] = $search;
		$total = Admin::getCount($search);
		$pager = Paginator::make(array(),$total,$pageSize);
		$pager->appends($search);
		$data['pagelinks'] = $pager->links();
		$data['is_super'] = true;
		return $this->display('admin_list',$data);
	}

	public function groupManage()
	{
		$current_user = $this->current_user;
		$group_ids = isset($current_user['menus_nodes']['group_ids']) ? $current_user['menus_nodes']['group_ids'] : array();
		$search['in_group_id'] = array_merge(array(0),$group_ids);
		$data['datalist'] = Admin::getList($search,1,50);
		$data['is_super'] = false;
		$groups = AuthGroup::getNameList();
		$groups[0] = '无';
		$data['groups'] = $groups;
		return $this->display('admin_list',$data);
	}
	
	public function getAdd()
	{
		$data = array();
		$groups = AuthGroup::getNameList();
		$data['groups'] = $groups;
		$data['is_super'] = AuthService::verifyNodeAuth('admin/admin/modify-pwd');		
		return $this->display('admin_info',$data);
	}
	
	public function getEdit($admin_id)
	{
		//if(!$admin_id) $admin_id = $this->current_user['id'];
		$data = array();
		$data['admininfo'] = Admin::getInfoById($admin_id);
		$groups = AuthGroup::getNameList();		
		$data['groups'] = $groups;
		$data['is_super'] = AuthService::verifyNodeAuth('admin/admin/modify-pwd');
		return $this->display('admin_info',$data);
	}
	
	public function postSave()
	{
		$input = Input::only('id','username','authorname','realname','group_id','password');
		if($input['id']){
			unset($input['username']);
		} else {
			unset($input['id']);
		}
		if(AuthService::verifyNodeAuth('admin/admin/modify-pwd')==false || empty($input['password'])){
			unset($input['password']);
		}
		if(isset($input['username'])&&$input['username']){
			$exists = Admin::getInfoByUsername($input['username']);
			if($exists){
				return $this->back('账号名已经存在');
			}
		}

		$result = Admin::saveInfo($input);
		if($result){
			return $this->redirect('admin/admin/list','账号保存成功');
		}else{
			return $this->back('账号保存成功');
		}
	}

	public function getDel($admin_id=0)
	{
		if($admin_id){
			$data['admininfo'] = Admin::getInfoById($admin_id);
			if ($data['admininfo']['isopen'] <> 2) {
				return $this->back('只能删除禁用状态的账号');
			}
			Admin::del($admin_id);
		}
		return $this->redirect('admin/admin/list','组权限删除成功');
	}
	
	public function getChangeStatus($admin_id,$status)
	{
		Admin::modifyStatus($admin_id, $status);
		return $this->redirect('admin/admin/list','账号保存成功');
	}

	public function getPermission($admin_id)
	{
		$admin = Admin::getInfoById($admin_id);
		$data['nodelist'] = array();
		$data['group_ids'] = array();
		$data['module_ids'] = array();
		$data['can_module_ids'] = array();
		$data['admin_id'] = $admin_id;
		$data['groups'] = AuthGroup::getList(1,20);
		$data['modules'] = Module::getList(1,50);
		if(isset($admin['menus_nodes']['nodes'])) {
			$data['nodelist'] = $admin['menus_nodes']['nodes'];
		}else{
			$group = AuthGroup::getInfo($admin['group_id']);
			if($group){
				$data['group'] = $group;
				$data['nodelist'] = $group['menus_nodes']['nodes'];
			}
		}
		if(isset($admin['menus_nodes']['group_ids'])) $data['group_ids'] = $admin['menus_nodes']['group_ids'];
		if(isset($admin['menus_nodes']['module_ids'])) $data['module_ids'] = $admin['menus_nodes']['module_ids'];
		$data['admin_all_menu'] = AuthService::getAllMenu();
		$data['can_module_ids'] = isset($this->current_user['menus_nodes']['module_ids']) ? $this->current_user['menus_nodes']['module_ids'] : array();
		return $this->display('admin_permission',$data);
	}

	public function postPermission()
	{
		$admin_id = Input::get('admin_id');
		$nodes = Input::get('node_id',array());
		$group_ids = Input::get('group_id',array());
		$module_ids = Input::get('module_id',array());
		$menu = AuthService::getAllMenu();
		$group_menus = array();
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
		$input['id'] = $admin_id;
		$input['menus_nodes'] = json_encode(array('menus'=>$group_menus,'nodes'=>$group_nodes,'group_ids'=>$group_ids,'module_ids'=>$module_ids));
		$success = Admin::saveInfo($input);
		if($success){
			return $this->redirect('admin/admin/permission/'.$admin_id,'权限保存成功');
		}else{
			return $this->back('权限保存失败');
		}
	}
}