<?php
namespace modules\a_yiyuan\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Base\AllService;
class HelpController extends BackendController
{
    public static $channelId = "android";
    public static function postAjaxDo()
    {
        $data = Input::get();
        $res = AllService::excute2("8089",$data,$data['url'],false);
        echo json_encode($res);
    }
}