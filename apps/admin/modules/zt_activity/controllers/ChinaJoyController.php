<?php
namespace modules\zt_activity\controllers;

use Illuminate\Support\Facades\Paginator;
use Youxiduo\Activity\Model\ChinaJoyBarrage;
use Youxiduo\Helper\Utility;
use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\System\SettingService;
use Youxiduo\Activity\Model\ChinaJoyGuide;
use Youxiduo\Activity\Model\ChinaJoyManufacturers;



class ChinaJoyController extends BackendController {
	public function _initialize() {
		$this->current_module = 'zt_activity';
	}

	/**
	 * 修改显示界面
	 * @param int $gid
	 */
	public function getEdit() {
		$data = array();
		$config = SettingService::getConfig('notice');
		if($config){
			$data['result'] = $config['data'];
		}
		return $this->display('chinajoy-edit',$data);
	}
	
	/**
	 * 保存
	 */
	public function postSave() {
        $parameter['widthnumble'] = Input::get('widthnumble',0);
		$parameter['context'] = Input::get('context');
		//验证规则
		$validator['widthnumble'] = 'required';
		//错误信息返回
		$errmessage['required'] = '不能为空';
		//验证
		$validator = Validator::make($parameter, $validator, $errmessage);
		if ($validator->fails()) {
			return $this->back()->withErrors($validator)->withInput();
		}
		SettingService::setConfig('notice', $parameter);
		return $this->redirect('zt_activity/chinajoy/edit','修改完成');
	}

    /**
     * 直播行程显示界面
     * @param int $gid
     */
    public function getDirectEdit() {
        $data = array();
        $config = SettingService::getConfig('direct');
        if($config){
            $data['result'] = $config['data'];
        }
        return $this->display('chinajoy-direct-edit',$data);
    }

    /**
     * 保存
     */
    public function postSaveDirect() {
        $parameter['content'] = Input::get('content');
//        print_r($parameter);exit;
        SettingService::setConfig('direct', $parameter);
        return $this->redirect('zt_activity/chinajoy/direct-edit','修改完成');
    }

    /**
     * 弹幕管理
     * @return mixed
     */
    public function getBarrage(){
        $keyword = Input::get('keyword','');
        $page = Input::get('page',1);
        $pagesize = 20;
        $result = ChinaJoyBarrage::getList($pagesize,$page, $keyword,false);
        $pager = Paginator::make(array(),$result['total'],$pagesize);
        $data['pagelinks'] = $pager->links();
        $data['result'] = $result['result'];
        return $this->display('chinajoy-barrage',$data);
    }

    public function getBarrageDel($id){
        $result = ChinaJoyBarrage::getDel($id);
        if($result){
            return $this->redirect('zt_activity/chinajoy/barrage','操作完成');
        }else{
            return $this->back('操作失败');
        }
    }

    /**
     * 跑会指南
     */

    public function getGuide(){
        $keyword = Input::get('keyword','');
        $page = Input::get('page',1);
        $pagesize = 20;
        $result = ChinaJoyGuide::getList($pagesize,$page, $keyword);
        foreach($result['result'] as &$v){
            $v['pics'] = Utility::getImageUrl($v['pics']);
        }
        $pager = Paginator::make(array(),$result['total'],$pagesize);
        $data['pagelinks'] = $pager->links();
        $data['result'] = $result['result'];
        return $this->display('chinajoy-guide',$data);
    }

    public function getGuideEdit($id = ''){
        $result = array();
        if($id){
            $result = ChinaJoyGuide::getDetail($id);
            $result['pics'] = Utility::getImageUrl($result['pics']);
        }
        return $this->display('chinajoy-guide-edit',$result);
    }

    public function getGuideDel($id){
        $result = ChinaJoyGuide::getDel($id);
        if($result){
            return $this->redirect('zt_activity/chinajoy/guide','操作完成');
        }else{
            return $this->back('操作失败');
        }
    }

    public function postSaveGuide(){
        $parameter = Input::all();
        $parameter['addtime'] = strtotime($parameter('addtime'));
        //验证规则
        $validator = array(
            'title'=>'required',
            'organizer'=>'required',
            'form'=>'required',
            'scale'=>'required|integer',
            'address'=>'required',
            'content'=>'required',
            'register'=>'required',
        );
        //错误信息返回
        $errmessage = array(
            'title'=>'不能为空',
            'organizer'=>'不能为空',
            'form'=>'不能为空',
            'scale.required'=>'不能为空',
            'scale.integer'=>'必须是数字',
            'address'=>'不能为空',
            'content'=>'不能为空',
            'register'=>'不能为空',
        );
        //验证
        $validator = Validator::make($parameter, $validator, $errmessage);
        if ($validator->fails()) {
            return $this->back()->withErrors($validator)->withInput();
        }
        $dir = '/userdirs/chinajoy/' . date('Y') .'/'. date('m').'/';
        $path = storage_path() . $dir;

        //列表图
        if(Input::hasFile('pics')){
            $file = Input::file('pics');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $pics = $dir . $new_filename . '.' . $mime;

        }
        isset($pics) && $parameter['pics'] = $pics;

        $result = ChinaJoyGuide::save($parameter);
        if($result){
            return $this->redirect('zt_activity/chinajoy/guide','操作完成');
        }else{
            return $this->back('操作失败');
        }
    }

    /**
     * 厂商专区
     */

    public function getManufacturers(){
        $keyword = Input::get('keyword','');
        $page = Input::get('page',1);
        $pagesize = 20;
        $result = ChinaJoyManufacturers::getList($pagesize,$page, $keyword);
        foreach($result['result'] as &$v){
            $v['icon'] = Utility::getImageUrl($v['icon']);
        }
        $pager = Paginator::make(array(),$result['total'],$pagesize);
        $data['pagelinks'] = $pager->links();
        $data['result'] = $result['result'];
        return $this->display('chinajoy-manufacturers',$data);
    }

    public function getManufacturersEdit($id = ''){
        $result = array();
        if($id){
            $result = ChinaJoyManufacturers::getDetail($id);
            $result['icon'] = Utility::getImageUrl($result['icon']);
        }
        return $this->display('chinajoy-manufacturers-edit',$result);
    }

    public function getManufacturersDel($id){
        $result = ChinaJoyManufacturers::getDel($id);
        if($result){
            return $this->redirect('zt_activity/chinajoy/manufacturers','操作完成');
        }else{
            return $this->back('操作失败');
        }
    }

    public function postSaveManufacturers(){
        Input::get('id') && $parameter['id'] = Input::get('id');
        $parameter['title'] = Input::get('title');
        $parameter['keyword'] = Input::get('keyword');
        $parameter['sort'] = Input::get('sort',100);

        //验证规则
        $validator = array(
            'title'=>'required',
            'keyword'=>'required',
            'sort'=>'numeric'
        );
        //错误信息返回
        $errmessage = array(
            'title'=>'不能为空',
            'keyword'=>'不能为空',
            'sort'=>'只能为数字'
        );
        //验证
        $validator = Validator::make($parameter, $validator, $errmessage);
        if ($validator->fails()) {
            return $this->back()->withErrors($validator->messages()->first())->withInput();
        }
        $dir = '/userdirs/chinajoy/' . date('Y') .'/'. date('m').'/';
        $path = storage_path() . $dir;

        //列表图
        if(Input::hasFile('icon')){
            $file = Input::file('icon');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $icon = $dir . $new_filename . '.' . $mime;

        }
        isset($icon) && $parameter['icon'] = $icon;
        $result = ChinaJoyManufacturers::save($parameter);
        if($result){
            return $this->redirect('zt_activity/chinajoy/manufacturers','操作完成');
        }else{
            return $this->back('操作失败');
        }


    }

}