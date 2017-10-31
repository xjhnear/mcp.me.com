<?php
namespace modules\v4message\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\MyHelp;
//use ApnsPHP\Push;
//use ApnsPHP\AbstractClass;
//use ApnsPHP\Message\BaiduCustom;

use modules\v4message\models\Push;
use Yxd\Services\UserService;

class PushController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'v4message';
    }

    public function getList()
    {
        $data = array();
        $page = Input::get('page', 1);
        $startdate = Input::get('startdate',null);
        $enddate = Input::get('enddate',null);
        $toUid = Input::get('toUid',null);
        $pagesize = 10;
        $search = array('deviceType'=>4,'pushType'=>0,'startdate'=>$startdate,'enddate'=>$enddate,'toUid'=>$toUid);
        $result = Push::search($search, $page, $pagesize);

        if(is_array($result['result'])){
            foreach($result['result'] as &$v){
                $v['platform'] = explode(',', $v['platform']);
                $platform_arr = array();
                foreach ($v['platform'] as $item) {
                    switch ($item) {
                        case 'yxdjqb':
                            $platform_arr[] = '玩家版';
                            break;
                        case 'youxiduojiu3':
                            $platform_arr[] = '业内版';
                            break;
                        case 'youxiduobeiyong2':
                            $platform_arr[] = '备用版';
                            break;
                        case 'ios':
                            $platform_arr[] = '玩家版';
                            $platform_arr[] = '备用版';
                            break;
                    }
                }
                $v['platform'] = implode(',', $platform_arr);
                if(isset($v['messages']['payload'])){
//                     echo $v['messages']['payload'];
                    $v['messages']['payload'] = json_decode($v['messages']['payload'],true);
                }
            }
        }

        $pager = Paginator::make(array(), $result['totalCount'], $pagesize);
        $pager->appends($search);
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        $data['datalist'] = $result['result'];
        return $this->display('push-list', $data);
    }

    public function getAdd()
    {
        $data = array();
        $data['linkTypeList'] = Push::$LinkTypeList;
        return $this->display('push-info',$data);
    }

    public function postSave()
    {
        $push = Input::get('is_push',0);
        $type = Input::get('linktype');
        $link = Input::get('link');
        $title = Input::get('title');
        $content = Input::get('content');
        $to_uids = Input::get('to_uids','');
        $msgtype = Input::get('msgtype');
        $gameId = Input::get('gameId');
        $other = Input::get('other');
        $appname = Input::get('appname');
        if (is_array($appname)) {
            $appname = implode(',', $appname);
        }
        if($to_uids){
            $uids = explode(',',$to_uids);
        }else{
            $uids = null;
        }
        $validator = Validator::make(array(
            'title'=>$title,
            'content'=>$content,
            'msgtype'=>$msgtype
        ),
            array(
                'title'=>'required',
                'content'=>'required',
                'msgtype'=>'required'
            ));
        if($validator->fails()){
            if($validator->messages()->has('title')){
                //return $this->back()->with('global_tips','标题不能为空');
            }
            if($validator->messages()->has('content')){
                return $this->back()->with('global_tips','内容不能为空');
            }
            if($validator->messages()->has('msgtype')){
                return $this->back()->with('global_tips','发送方式不能为空');
            }
        }
        $allUser = false;
        if($msgtype == 1 && !$uids){
            return $this->back()->with('global_tips','定向发送方式下，用户UID不能为空');
        }

        if($msgtype == 2){
            $uids = null;
            $to_uids = "0";
            $allUser = true;
        }
        //NoticeService::sendInitiativeMessage($type, $linktype, $link, $title, $content,0,$push,$uids);

        $tokens = null;
        $linkId= '';
        if($type=='webview'){
            $linkType = 2;
        }elseif($type=='outredirect'){
            $linkType = 1;
        }else{
            $linkType = 0;
            $linkId = $type;
        }

        $linkValue = $link;

//        if($uids){
//            $tokens = array();
//            $res = UserService::getTokenList($uids,false);
//            foreach($res as $uid=>$token){
//                $tokens[] = $uid . ';' . $token;
//            }

//        }
//        print_r(Input::get());
        Push::sendMessage($content,$linkType,$linkId,$linkValue,$allUser,$to_uids,$gameId,'',$appname);

        return $this->redirect('v4message/push/list');
    }

    public function getAutosearch()
    {
        $key=Input::all();
        $arr['results']=MyHelp::getAutoSearch($key['key'],$key['variableName']);
        foreach($arr['results'] as &$v){
            if(isset($v['gameId'])){
                $v['info'] = $v['gameId'];
            }
        }

        echo json_encode($arr);
        exit;
    }


}