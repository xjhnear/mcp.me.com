<?php
/**
 * Created by PhpStorm.
 * User: ganlin
 * Date: 17/10/13
 * Time: 下午3:42
 */

namespace modules\v4_product\controllers;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\MyHelp;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Session;

class WechatController  extends BackendController
{
    const WECHAT_API_URL = 'app.wechat_api_url';
    public function _initialize()
    {
        $this->current_module = 'v4_product';
    }

    public function getList()
    {
        $data['processStatusArr'] = array(
            '' => '全部',
            'U' => '未处理',
            'P' => '通过',
            'R' => '拒绝',
        );

        $input['uid'] = Input::get('uid');
        $input['startTime'] = Input::get('startTime');
        $input['startTime'] && $input['startTime'] = strtotime($input['startTime'])*1000;
        $input['endTime'] = Input::get('endTime');
        $input['endTime'] && $input['endTime'] = strtotime($input['endTime'])*1000;
        $input['processStatus'] = Input::get('processStatus');
        $page = Input::get('page', 1);
        $input['pageSize'] = (integer)Input::get('pageSize', 10);
        $input['startOffset'] = ( $page - 1) * $input['pageSize'];

        $input = array_filter($input);

        $res = MyHelp::html_form_data(Config::get(self::WECHAT_API_URL).'Manage/GetWithdrawList', $input);
//        dd($res['result']['list']);
//        dd($res);
        if ($res['errorCode'] == 0 && isset($res['result'])) {
            $total = $res['result']['totalCount'];
            $result = $res['result']['list'];

            foreach ($result as &$item) {
                isset($item['applyTime']) && $item['applyTime'] && $item['applyTime'] = date('Y-m-d H:i:s', (int)($item['applyTime']/1000));
                isset($item['processTime']) && $item['processTime'] && $item['processTime'] = date('Y-m-d H:i:s', (int)($item['processTime']/1000));
            }
        } else {
            $total = 0;
            $result= array();
        }

        $pager = Paginator::make(array(), $total, $input['pageSize']);
        isset($input['startTime']) && $input['startTime'] && $input['startTime'] = date('Y-m-d H:i:s', $input['startTime']/1000);
        isset($input['endTime']) && $input['endTime'] && $input['endTime'] = date('Y-m-d H:i:s', $input['endTime']/1000);
        $pager->appends($input);
        $data['search'] = $input;
        $data['pagelinks'] = $pager->links();
        $data['datalist'] = $result;

        return $this->display('wechat/list',$data);
    }

    public function postAjaxPass () {
        $data['withdrawId'] = Input::get('id');
        if (!$data['withdrawId'])
            return json_encode(array('success'=>"false",'mess'=>'缺少ID'));
        $SESSION = Session::get('youxiduo_admin');
        $data['operatorName'] = $SESSION['realname'];

        $res = MyHelp::html_form_data(Config::get(self::WECHAT_API_URL).'Manage/WithdrawPass', $data);

        if($res['errorCode'] == 0){
            return json_encode(array('success'=>"true",'mess'=>'操作成功'));
        }else{
            return json_encode(array('success'=>"false",'mess'=>$res['errorDescription']));
        }
    }

    public function postAjaxRefuse () {
        $data['withdrawId'] = Input::get('id');
        if (!$data['withdrawId'])
            return json_encode(array('success'=>"false",'mess'=>'缺少ID'));
        $SESSION = Session::get('youxiduo_admin');
        $data['operatorName'] = $SESSION['realname'];

        $res = MyHelp::html_form_data(Config::get(self::WECHAT_API_URL).'Manage/WithdrawRefuse', $data);

        if($res['errorCode'] == 0){
            return json_encode(array('success'=>"true",'mess'=>'操作成功'));
        }else{
            return json_encode(array('success'=>"false",'mess'=>$res['errorDescription']));
        }
    }


}