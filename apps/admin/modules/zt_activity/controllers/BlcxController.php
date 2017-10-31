<?php
namespace modules\zt_activity\controllers;

use Illuminate\Support\Facades\Config;
use Youxiduo\Activity\Blcx\BlcxService;

use Illuminate\Support\Facades\Input;
use Youxiduo\Helper\Utility;
use Yxd\Modules\Core\BackendController;
use Youxiduo\User\AccountService;
use Youxiduo\Activity\Model\ActivityBlcxComment;
use Youxiduo\Activity\Model\ActivityBlcxPreson;


class BlcxController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'zt_activity';
	}

    //获取留言列表
	public function getMessageList()
	{
        $data['comment'] = ActivityBlcxComment::getLists(-1);
        foreach($data['comment'] as &$v){
            if($v['uid']){
                $user = AccountService::bbsGetUserInfo($v['uid']);
            }
            $v['username'] = empty($user['nickname']) ? '匿名玩家' : $user['nickname'];
        }
		return $this->display('blcx-message-list',$data);
	}

    //审核
    public function getMessageOpen($id){
        if(!$id) return $this->back('请传ID');
        $result = ActivityBlcxComment::audit($id, array('audit'=>1));
        if($result){
           return $this->back('修改成功');
        }else{
           return $this->back('修改失败');
        }
    }
    //关闭
    public function getMessageClose($id){
        if(!$id) return $this->back('请传ID');
        $result = ActivityBlcxComment::audit($id, array('audit'=>0));
        if($result){
            return $this->back('修改成功');
        }else{
            return $this->back('修改失败');
        }
    }


    //获取月儿海选列表
    public function getAuditList(){
        $data['preson'] = BlcxService::getAuditList(-1);
        return $this->display('blcx-audit-list',$data);
    }

    //审核
    public function getListOpen($id){
        if(!$id) return $this->back('请传ID');
        $result = ActivityBlcxPreson::save(array('id'=>$id,'audit'=>1));
        if($result){
            return $this->back('修改成功');
        }else{
            return $this->back('修改失败');
        }
    }
    //关闭
    public function getListClose($id){
        if(!$id) return $this->back('请传ID');
        $result = ActivityBlcxPreson::save(array('id'=>$id,'audit'=>0));
        if($result){
            return $this->back('修改成功');
        }else{
            return $this->back('修改失败');
        }
    }
    //移动图片
    public function getMove($id){
        if(!$id) return $this->back('请传ID');
        //查询
        $old_result = ActivityBlcxPreson::getFirst($id);
        if(!$old_result) return $this->back('没有找到此项');
        $result = ActivityBlcxPreson::save(array('id'=>$id,'singlepic'=>$old_result['pics']));
        if($result){
            return $this->back('修改成功');
        }else{
            return $this->back('修改失败');
        }
    }

	public function getEdit($id)
	{
		$data = array();
		$activity = ActivityBlcxPreson::getFirst($id);
        $activity['pics'] = Utility::getImageUrl($activity['pics']);
        $activity['singlepic'] = Utility::getImageUrl($activity['singlepic']);
		$data['activity'] = $activity;
		return $this->display('blcx-audit-edit',$data);
	}

    public function postEdit(){
        $input  = Input::only('id','name','votes','audit');
        $dir = '/userdirs/activity/blcx/' . date('Y') .'/'. date('m').'/';
        $path = storage_path() . $dir;
        //列表图
        if(Input::hasFile('singlepic')){
            $file = Input::file('singlepic');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $input['singlepic'] = $dir . $new_filename . '.' . $mime;
        }
        $result = ActivityBlcxPreson::save($input);
        if($result){
            $tips = '修改成功';
        }else{
            $tips = '修改失败';
        }
        return $this->redirect('zt_activity/blcx/audit-list')->with('global_tips',$tips);
    }

}