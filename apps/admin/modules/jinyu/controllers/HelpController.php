<?php
namespace modules\jinyu\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Base\AllService;
class HelpController extends BackendController
{
    public static function postAjaxDo()
    {
        $data = Input::get();
        $res = AllService::excute2("jinyu",$data,$data['url'],false);
        echo json_encode($res);
    }
}