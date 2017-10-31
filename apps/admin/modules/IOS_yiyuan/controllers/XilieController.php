<?php
namespace modules\IOS_yiyuan\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelpLx;
use Youxiduo\Base\AllService;


class XilieController extends HelpController
{
    public function _initialize()
    {
        $this->current_module = 'IOS_yiyuan';
    }

    public function getList()
    {
        $data = $search = array();
        $search['pageSize']  = 10;
        $search['channelId'] = self::$channelId;
        $total = 0;
        $search['seriesName'] = Input::get('seriesName');
        $pageIndex = (int)Input::get('page', 1);
        $search['offset'] = ($pageIndex-1) * $search['pageSize'];
        $res = AllService::excute2("8089",$search,"luckyDraw/QuerySeriesMerchandise");
        $data['list'] = array();
        if($res['success']){
            $resData = json_decode($res['data'], true);
            if (isset($resData['list'])) {
                $data['list'] = $resData['list'];
                $total = $resData['totalCount'];
            }
        }
        $data['pagelinks'] = MyHelpLx::pager(array(),$total,$search['pageSize'],$search);
        $data['search'] = $search;
        return $this->display('series-goods-list', $data);
    }

    public function getAdd()
    {
        $input['id'] = Input::get('id', false);
        $data['data'] = array();
        if($input['id']){
            $res = AllService::excute2("8089",$input,"luckyDraw/QuerySeriesMerchandiseDetail");
            if (!$res['success']) return $this->back()->with('global_tips','详情接口错误，请重试或联系开发人员');

            $xilieRes = json_decode($res['data'], true);
            if ($xilieRes) {
                $xilieRes['merchandiseIds'] = explode(',', $xilieRes['merchandiseIds']);
                $xilieRes['goodsArr'] = array();
                foreach ($xilieRes['merchandiseIds'] as $key => $value) {
                    $tempId['id'] = $value;
                    $tempId['channelId'] = self::$channelId;
                    $tempRes = AllService::excute2("8089",$tempId,"luckyDraw/QueryMerchandiseDetail");
                    if ($tempRes['success']) {
                        $detailRes = json_decode($tempRes['data'], true);
                        $xilieRes['goodsArr'][$key]['id'] = $detailRes['id'];
                        $xilieRes['goodsArr'][$key]['title'] = $detailRes['title'];
                    } else {
                        $xilieRes['goodsArr'][$key]['id'] = $value;
                        $xilieRes['goodsArr'][$key]['title'] = '';
                    }

                }

                $data['data'] = $xilieRes;
            }
        }
        return $this->display('series-goods-add',$data);
    }

    public function postAdd()
    {
        $id = Input::get("id", false);
        $input['seriesName']        = Input::get('seriesName');
        $input['channelId']         = self::$channelId;
        $input['merchandiseIds']    = Input::get('merchandiseIds');

        //验证规则
        $rule = array(
            'seriesName'=>'required',
            'merchandiseIds'=>'required',
        );
        $prompt = array(
            'seriesName.required'=>'请填写系列名称',
            'merchandiseIds.required'=>'请添加商品',
        );
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }

        $input['merchandiseIds'] && $input['merchandiseIds']  = implode(',', $input['merchandiseIds']);
        $input['id'] = $id;

        if($id){
            $res= AllService::excute2("8089",$input,"luckyDraw/UpdateSeriesMerchandise",false);
        }else{
            unset($input['id']);
            $res= AllService::excute2("8089",$input,"luckyDraw/CreateSeriesMerchandise",false);
        }

        if($res['success']){
            if ($id) {
                return $this->redirect('a_yiyuan/xilie/list','修改成功');
            } else {
                return $this->redirect('IOS_yiyuan/xilie/list','添加成功');
            }

        }else{
            return $this->back($res['error']);
        }
    }

    public function getGoodsSearch (){
        $input = array();
        $input['pageSize'] = 6;
        $input['title'] = Input::get('keyword');

        $page = Input::get('page',1);
        $input['offset'] = ($page-1) * $input['pageSize'];
        $input['channelId'] = self::$channelId;
        $data = array();
        $result = AllService::excute2("8089",$input,"luckyDraw/QueryMerchandise");
        if ($result['success']) {
            $resData = json_decode($result['data'], true);
            if (isset($resData['list'])) {
                $data['data'] = $resData['list'];
                $total = $resData['totalCount'];
            }
        }

        $pager = Paginator::make(array(),$total ,$input['pageSize']);
        $pager->appends($input);
        $data['search'] = $input;
        $data['pagelinks'] = $pager->links();
        $html = $this->html('pop-goods-list',$data);
        return $this->json(array('html'=>$html));
    }

}