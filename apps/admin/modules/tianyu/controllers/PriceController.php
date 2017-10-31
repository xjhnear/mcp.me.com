<?php
namespace modules\tianyu\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Youxiduo\V4\User\UserService;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelpLx;
use Youxiduo\Base\AllService;
use modules\tianyu\controllers\HelpController;


class PriceController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'tianyu';
    }

    public function getList()
    {
        $data = $search = array();
        $total = '';
        $pageSize = 10;
        $search = Input::get();
        $search['pageSize'] = $pageSize;
        $pageIndex = (int)Input::get('page', 1);
        $search['offset'] = ($pageIndex - 1) * 10;
        $res = AllService::excute2("8338", $search, "tianyu_lottery/search/search_price_info");

        $data['list'] = array();
        if ($res['success']) {
                $data['list'] = $res['data'];
//            $total = $res['totleCount'];
        }
        $data['pagelinks'] = MyHelpLx::pager(array(),$total,$search['pageSize'],$search);
        return $this->display('price-list', $data);
    }

    public function getAdd()
    {
        $data = array();
        $input = Input::get();
        $input['priceId'] = Input::get('priceId');
        if($input['priceId']){
            $res = AllService::excute2("8338",$input,"tianyu_lottery/search/search_price_info");
            if (!$res['success']) return $this->back()->with('global_tips','详情接口错误，请重试或联系开发人员');
            if(!empty($res['data'])){
                $data['data'] = $res['data'][0];
            }
        }
        return $this->display('price-form',$data);
    }

    public function postAdd()
    {
        $input = Input::all();
        $input['priceId']         = Input::get("priceId");
        $input['priceTitle']       = Input::get('priceTitle');
        $input['priceSort']        = Input::get('priceSort');
        if(Input::file('pricePic')){
            $input['pricePic'] = Input::file('pricePic');
            if($input['pricePic']){
                $input['pricePic'] = MyHelpLx::save_img($input['pricePic']);
            }
        }
        if($input['priceId']){
            $res= AllService::excute2("8338",$input,"tianyu_lottery/update/update_priceInfo",false);
        }else{
            unset($input['id']);
            $res= AllService::excute2("8338",$input,"tianyu_lottery/add/add_priceInfo",false);
        }
        if($res['success']){
            if ($input['priceId']) {
                return $this->redirect('tianyu/price/list','修改成功');
            } else {
                return $this->redirect('tianyu/price/list','添加成功');
            }
        }else{
            return $this->back($res['error']);
        }
    }


}