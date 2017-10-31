<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/25
 * Time: 10:53
 */
namespace modules\a_yiyuan\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelpLx;
use Youxiduo\Base\AllService;
use modules\a_yiyuan\controllers\HelpController;


class UsermessageController extends BackendController
{
    public function getadd(){
        $data = array();
        $res = TaskV3Service::task_rule(array());
        if(!$res['errorCode']&&$res['result']){
            $data['data'] = $res['result'];
        }
        return $this->display('usermessage-add',$data);
    }
    public function postTaskRuleUpdate () {
        $data['taskRule'] = Input::get('content');
        if ($data['taskRule']) {
            $res = TaskV3Service::update_task_rule($data);
            if(!$res['errorCode'] && $res['result']){
                echo json_encode(array('success'=>"true",'msg'=>'修改成功','data'=>""));
            }else{
                echo json_encode(array('success'=>"false",'msg'=>'修改失败','data'=>""));
            }
        }
    }

}