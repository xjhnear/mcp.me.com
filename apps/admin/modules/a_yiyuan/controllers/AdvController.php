<?php
namespace modules\a_yiyuan\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelpLx;
use Youxiduo\Base\AllService;


class AdvController extends HelpController
{
    public static $typeArr = array(
        'spin' => '轮播',
        'recommend' => '推荐',
        'duobao' => '夺宝规则',
        'calculate' => '计算',
    );

    public static $linkType = array(
        '1' => '商品详情',
        '2' => '系列详情',
    );

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
        $search['type'] = Input::get('type');
        if ($search['type']) {
            $pageIndex = (int)Input::get('page', 1);
            $search['offset'] = ($pageIndex-1) * $search['pageSize'];
            $search['channelId'] = self::$channelId;
            $res = AllService::excute2("8089",$search,"luckyDraw/QueryAdConfigInfo");
            $data['list'] = array();
            if($res['success']){
                $resData = json_decode($res['data'], true);
                if (isset($resData['list'])) {
                    $data['list'] = $resData['list'];
                    foreach ($resData['list'] as $key => $value) {
                        $data['list'][$key]['startTime'] = date('Y-m-d H:i:s', intval($value['startTime']/1000));
                        $data['list'][$key]['endTime'] = date('Y-m-d H:i:s', intval($value['endTime']/1000));
                    }
                    $total = $resData['totalCount'];
                }
            }
            $data['pagelinks'] = MyHelpLx::pager(array(),$total,$search['pageSize'],$search);
        }

        $data['search'] = $search;
        $data['typeArr'] = self::$typeArr;

        return $this->display('ad-list',$data);
    }

    public function getAdd()
    {
        $data = array();
        $input['id'] = Input::get('id', '');
        if ($input['id']){
            $res = AllService::excute2("8089",$input,"luckyDraw/QueryAdConfigInfoDetail");
            if (!$res['success']) return $this->back()->with('global_tips','详情接口错误，请重试或联系开发人员');
            $detailRes = json_decode($res['data'], true);
            if ($detailRes) {
                if (isset($detailRes['startTime']) && $detailRes['startTime'])
                    $detailRes['startTime'] = date('Y-m-d H:i:s',intval($detailRes['startTime']/1000));

                if (isset($detailRes['endTime']) && $detailRes['endTime'])
                    $detailRes['endTime'] = date('Y-m-d H:i:s', intval($detailRes['endTime']/1000));

                if ($detailRes['type'] == 'spin' || $detailRes['type'] == 'recommend') {
                    $tempId['id'] = $detailRes['linkValue'];
                    $tempId['channelId'] = self::$channelId;

                    //商品详情
                    if ($detailRes['linkType'] == '1') {
                        $tempRes = AllService::excute2("8089",$tempId,"luckyDraw/QueryMerchandiseDetail");
                        if ($tempRes['success']) {
                            $tempJsonRes = json_decode($tempRes['data'], true);
                            $detailRes['linkName'] = $tempJsonRes['title'];
                        }
                        //系列商品
                    } elseif ($detailRes['linkType'] == '2') {
                        $tempRes = AllService::excute2("8089",$tempId,"luckyDraw/QuerySeriesMerchandiseDetail");

                        if ($tempRes['success']) {
                            $tempJsonRes = json_decode($tempRes['data'], true);
                            $detailRes['linkName'] = $tempJsonRes['seriesName'];
                        }
                    }

                }

                $data['data'] = $detailRes;
            }
        }

        $data['typeArr'] = self::$typeArr;
        $data['linkType'] = self::$linkType;
        return $this->display('ad-add',$data);
    }

    public function postAdd()
    {
        $id = Input::get("id", false);
        $input['channelId'] = self::$channelId;
        $input['type']      = Input::get('type');
        $input['title']     = Input::get('title');
        $input['idx']       = Input::get('idx');
        $input['recommendImg'] = Input::file('recommendImg');
        $startTime          = Input::get('startTime');
        $endTime            = Input::get('endTime');

        //有新图片就替换，没有就继续用老的图片
        if (Input::file('recommendImg')) {
            $recommendImg = MyHelpLx::save_img($input['recommendImg']);
            $input['recommendImg'] = $recommendImg ? $recommendImg : '';
            unset($titleImg);
        } else {
            $input['recommendImg'] = Input::get('recommendImg_old');
            unset($input['recommendImg_old']);
        }

        $input['startTime'] = $startTime ? strtotime($startTime)*1000 : 0;
        $input['endTime']   = $endTime ? strtotime($endTime)*1000 : 0;
        $input['enable']    = Input::get('enable');
        $input['linkType']  = Input::get('linkType');
        $input['linkValue'] = Input::get('linkValue');
        $input['id'] = $id;

        if($id){
            $res= AllService::excute2("8089",$input,"luckyDraw/UpdateAdConfigInfo",false);
        }else{
            unset($input['id']);
            $res= AllService::excute2("8089",$input,"luckyDraw/CreateAdConfigInfo",false);
        }

        if($res['success']){
            $type = $input['type'];
            $type = $type ? $type : '';
            if ($id) {
                return $this->redirect('a_yiyuan/adv/list?type=' . $type,'添加成功');
            } else {
                return $this->redirect('a_yiyuan/adv/list?type=' . $type,'添加成功');
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

    public function getSeriesGoodsSearch (){
        $input = array();
        $input['pageSize'] = 6;
        $input['seriesName'] = Input::get('keyword');

        $page = Input::get('page',1);
        $input['offset'] = ($page-1) * $input['pageSize'];
        $input['channelId'] = self::$channelId;
        $data = array();
        $result = AllService::excute2("8089",$input,"luckyDraw/QuerySeriesMerchandise");
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
        $html = $this->html('pop-series-goods-list',$data);
        return $this->json(array('html'=>$html));
    }

    public function getEdit () {
        $id     = Input::get('id');
        $val    = Input::get('val');

        $input['id'] = $id;
        //详情
        $resDetail = AllService::excute2("8089",$input,"luckyDraw/QueryAdConfigInfoDetail");
        if(!$resDetail['success']) {
            echo json_encode(array('success'=>400, 'msg'=>'未找到信息', 'data'=>''));
        }
        $resDetail = json_decode($resDetail['data'], true);
        $resDetail['enable'] = (string)$val;
        $resDetail['channelId'] = self::$channelId;

        //修改
        $resUpdate = AllService::excute2("8089",$resDetail,"luckyDraw/UpdateAdConfigInfo");

        if($resUpdate['success']){
            echo json_encode(array('success'=>200, 'msg'=>'修改成功', 'data'=>$val));
        } else {
            echo json_encode(array('success'=>400, 'msg'=>'修改失败', 'data'=>''));
        }

    }

}