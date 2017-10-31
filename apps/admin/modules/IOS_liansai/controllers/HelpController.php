<?php
namespace modules\yxvl_eSports\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Cms\WebGameService;
use Youxiduo\ESports\ESportsService;

class HelpController extends BackendController
{
    //提供select数据
    public static function getCategoryArr($search=array(),$api="Article"){
        $catalogs = array();
        $res= ESportsService::excute($search,"Get".$api."Catalogs",true);
        if($res['success']){
            foreach($res['data'] as $item){
                $catalogs[$item['url']] = $item['name'];
            }
        }
        return $catalogs;
    }
}