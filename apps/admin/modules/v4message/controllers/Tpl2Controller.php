<?php
namespace modules\v4message\controllers;

use Illuminate\Support\Facades\Input;
use Yxd\Modules\Core\BackendController;

use Youxiduo\Helper\MyHelpLx;
use Youxiduo\Base\AllService;


class Tpl2Controller extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'v4message';
	}
	
    public function getList()
	{
		$data = array();
        $input = Input::get();
		$res = AllService::excute2('PUSH',$input,"GetPushMessageTemplateList");
        if($res['success']){
            $data['list'] = $res['data'];
        }
		return $this->display('tpl2/tpl-list',$data);
	}
	
    public function getSave()
	{
		$data = array();
        $id = Input::get("id","");
        if($id){
            $res = AllService::excute2('PUSH',array('id'=>$id),"GetPushMessageTemplateList");
            if(isset($res['data'][0])){
                $data['data'] = $res['data'][0];
            }
        }
		return $this->display('tpl2/tpl-info',$data);
	}
	
    public function postSave()
	{
		$input = Input::only('id','title','messageType','content');
        if($input['id']){
            $result = AllService::excute2('PUSH',$input,"UpdatePushMessageTemplate");
        }else{
            unset($input['id']);
            $result = AllService::excute2('PUSH',$input,"AddPushMessageTemplate");
        }

		if($result['success']){
			return $this->redirect('v4message/tpl2/list')->with('global_tips','保存成功');
		}else{
			return $this->back($result['error']);
		}

	}
}