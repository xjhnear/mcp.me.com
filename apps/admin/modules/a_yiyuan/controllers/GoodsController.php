<?php
namespace modules\a_yiyuan\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelpLx;
use Youxiduo\Base\AllService;


class GoodsController extends HelpController
{
    public function _initialize()
    {
        $this->current_module = 'a_yiyuan';
    }

    public function getList()
    {
        $data = $search = array();
        $search['pageSize'] = 10;
        $total = 0;
        $search['title'] = Input::get('title');
        $search['status'] = Input::get('status');
        $pageIndex = (int)Input::get('page', 1);
        $search['offset'] = ($pageIndex-1) * $search['pageSize'];
        $search['channelId'] = self::$channelId;

        $res = AllService::excute2("8089",$search,"luckyDraw/QueryMerchandise");
        $data['list'] = array();
        if($res['success']){
            $resData = json_decode($res['data'], true);
            if (isset($resData['list'])) {
                $data['list'] = $resData['list'];
                foreach ($data['list'] as &$row) {
                    $row['numberPrice'] && $row['numberPrice'] = intval($row['numberPrice']);
                    $row['diamondsPrice'] && $row['diamondsPrice'] = intval($row['diamondsPrice']);
                    $row['maxStandTimePreDraw'] && $row['maxStandTimePreDraw'] = $row['maxStandTimePreDraw']/(24*60*60*1000);
                }
                $total = $resData['totalCount'];
            }
        }
        $data['pagelinks'] = MyHelpLx::pager(array(),$total,$search['pageSize'],$search);
        $data['search'] = $search;
        return $this->display('goods-list',$data);
    }

    public function getAdd()
    {
        $data = array();
        $input['id'] = Input::get('id',"");
        if ($input['id']){
            $input['channelId'] = self::$channelId;
            $res = AllService::excute2("8089",$input,"luckyDraw/QueryMerchandiseDetail");
            if (!$res['success']) return $this->back()->with('global_tips','详情接口错误，请重试或联系开发人员');

            $detailRes = json_decode($res['data'], true);

            if ($detailRes) {
                $detailRes['imgs'] = explode(',', $detailRes['imgs']);
                $detailRes['startTime'] && $detailRes['startTime'] = date('Y-m-d H:i:s', $detailRes['startTime']/1000);
                $detailRes['endTime'] && $detailRes['endTime'] = date('Y-m-d H:i:s', $detailRes['endTime']/1000);
                $detailRes['fixedBuyCount'] && $detailRes['fixedBuyCount'] = json_decode($detailRes['fixedBuyCount'], true);
                $detailRes['templateName'] = '';

                if ($detailRes['templateId']) {
                    $tempId['templateId'] = $detailRes['templateId'];

                    $tempRes = AllService::excute2("8089",$tempId,"luckyDraw/QueryReceiveTemplateDetail");

                    if ($tempRes['success']) {
                        isset($tempRes['data']['templateName']) && $detailRes['templateName'] = $tempRes['data']['templateName'];
                    }
                }
                $detailRes['maxStandTimePreDraw'] = $detailRes['maxStandTimePreDraw']/(24*60*60*1000);
                $data['data'] = $detailRes;
            }
        }

        return $this->display('goods-add',$data);
    }

    public function postAdd()
    {
        $id = Input::get("id", false);
        $input = Input::all();
        $input['channelId'] = self::$channelId;
        $input['maxNumberPreUser'] = 0;
        $input['startTime'] = strtotime($input['startTime']) * 1000;
        $input['endTime']   = strtotime($input['endTime']) * 1000;
        if(Input::get('maxStandTimePreDraw')){
            $input['maxStandTimePreDraw'] = $input['maxStandTimePreDraw']*24*60*60*1000;
        }
        if (!$input['numberPrice']) $input['numberPrice'] = 0;
        //有新图片就替换，没有就继续用老的图片
        if (Input::file('titleImg')) {
            $titleImg = MyHelpLx::save_img($input['titleImg']);
            $input['titleImg'] = $titleImg ? $titleImg : '';
            unset($titleImg);
        } else {
            $input['titleImg'] = $input['titleImg_old'];
        }
        unset($input['titleImg_old']);

        $imgObjArr = Input::file("imgs");
        if (isset($input['imgs']) && $input['imgs']) {
            foreach ($input['imgs'] as $key => $value) {
                //有新图片就替换，没有就继续用老的图片
                if ($imgObjArr[$key]) {
                    $tempImg[] = MyHelpLx::save_img($input['imgs'][$key]);
                } else {
                    $tempImg[] = $input['imgs_old'][$key];
                }

            }
            $input['imgs'] = implode(',', $tempImg);
        }

        unset($tempImg);
        unset($input['imgs_old']);

        if($input['fixedBuyCount'][0] && $input['fixedBuyCount'][1] && $input['fixedBuyCount'][2]){
            $input['fixedBuyCount'] = json_encode($input['fixedBuyCount']);
        } else {
            $input['fixedBuyCount'] = '';
        }

        if($id){
            $res= AllService::excute2("8089",$input,"luckyDraw/UpdateMerchandise",false);
        }else{
            unset($input['id']);
            $input['totalCount'] = $input['count'];
            $res= AllService::excute2("8089",$input,"luckyDraw/CreateMerchandise",false);
        }

        if($res['success']){
            if ($id) {
                return $this->redirect('a_yiyuan/goods/list','修改成功');
            } else {
                return $this->redirect('a_yiyuan/goods/list','添加成功');
            }

        }else{
            return $this->back($res['error']);
        }
    }

    public function getEdit () {
        $id     = Input::get('id');
        $status = Input::get('statusData');

        $input['id'] = $id;
        $input['status'] = $status;
        //修改
        $resUpdate = AllService::excute2("8089",$input,"luckyDraw/UpdateMerchandiseStatus");

        if($resUpdate['success']){
            echo json_encode(array('success'=>200, 'msg'=>'修改成功', 'data'=>$status));
        } else {
            echo json_encode(array('success'=>400, 'msg'=>'可以添加', 'data'=>''));
        }

    }

    public function getDel () {
        $input['id'] = Input::get('id');

        //删除
        $res = AllService::excute2("8089",$input,"luckyDraw/DeleteMerchandise");

        if($res['success']){
            echo json_encode(array('success'=>200, 'msg'=>'删除成功'));
        } else {
            echo json_encode(array('success'=>400, 'msg'=>'删除失败'));
        }
    }


    public function getTemplateSearch()
    {
        $input = array();
        $input['pageSize'] = 6;
        $input['templateName'] = Input::get('keyword');

        $page = Input::get('page',1);
        $input['offset'] = ($page-1) * $input['pageSize'];
        $input['channelId'] = self::$channelId;
        $data = array();
        $result = AllService::excute2("8089",$input,"luckyDraw/QueryReceiveTemplateForPage");
        if ($result['success']) {
            $resData = $result['data'];
            if (isset($resData['list'])) {
                $data['data'] = $resData['list'];
                //默认模板
                $defaultRes = AllService::excute2("8089",$input,"luckyDraw/QueryDefaultReceiveTemplate");
                if ($defaultRes['success']) {
                    $defaultResData = json_decode($defaultRes['data'], true);
                    $defaultResData['template_name'] && $defaultResData['templateName'] = $defaultResData['template_name'];
                    $data['data'] = array_merge(array($defaultResData), $data['data']);
                }
                $total = $resData['totalCount'];
            }
        }
        $pager = Paginator::make(array(),$total ,$input['pageSize']);
        $pager->appends($input);
        $data['search'] = $input;
        $data['pagelinks'] = $pager->links();
        $html = $this->html('pop-template-list',$data);
        return $this->json(array('html'=>$html));
    }

}