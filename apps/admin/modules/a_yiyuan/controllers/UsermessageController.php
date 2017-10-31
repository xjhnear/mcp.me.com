<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/25
 * Time: 11:24
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

    public function _initialize()
    {
        $this->current_module = 'a_yiyuan';
    }

    public function getAdd()
    {
        $data = array();
        $input = Input::get();
        $res = AllService::excute2("8089",$input,"luckyDraw/QueryUserAgreement");
        if($res['data']){
            $data['data'] = $res['data'];
        }
        return $this->display('usermessage-add',$data);
    }


    public function postAdd()
    {
        $input = Input::all();
        $input['handleType'] = "U";
        $res = AllService::excute2("8089", $input, "luckyDraw/SaveOrUpdateUserAgree", false);
        if ($res['success']) {
            return $this->redirect('a_yiyuan/usermessage/add', '添加成功');
        } else {
            return $this->back($res['error']);

        }
    }



}