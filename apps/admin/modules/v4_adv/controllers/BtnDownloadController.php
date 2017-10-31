<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/15
 * Time: 11:25
 */
namespace modules\v4_adv\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;
use modules\v4_adv\models\BtnDownload;
use modules\game\models\GameModel;

class BtnDownloadController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'v4_adv';
    }

    public function getList()
    {
        $page = Input::get('page', 1);
        $pagesize = 10;
        $data =$search =  array();
        $search['pageIndex'] = $page;
        $search['pageSize'] = $pagesize;
//        $search['gameName'] = Input::get('gameName');
        $str = "";
        if(Input::get('gameName')){
            $res = GameModel::search(array('gname'=>Input::get('gameName')));
            if($res['result']) {
                foreach ($res['result'] as $v) {
                    if (isset($v['id'])) {
                        $str .= $v['id'] . ',';
                    }
                }
            }
            $str = substr($str,0,-1);
            $search['gid'] = $str;
        }

        $result = BtnDownload::getListNew($search);

        $pager = Paginator::make(array(), $result['totalCount'], $pagesize);
        $data['pagelinks'] = $pager->links();
        $data['datalist'] = $result['result'];
        $data['linkType'] = array('2'=>'内置浏览器','1'=>'Safari浏览器');
        return $this->display('btn-download-list', $data);
    }

    public function getAdd()
    {
        $data = array();
        $data['appType'] = array('yxdjqb'=>'游戏多','youxiduojiu3'=>'游戏多业内版','glwzry'=>'攻略');
        return $this->display('btn-download-info', $data);
    }

    public function getEdit($id)
    {
        $data = array();
        $tpl = BtnDownload::getInfo($id);
        if (!isset($tpl['thirdVendorsList'])) {
            $tpl['thirdVendorsList'] = array();
        }
        $tpl['thirdCount'] = count($tpl['thirdVendorsList']);
        $data['tpl'] = $tpl;
        $data['appType'] = array('yxdjqb'=>'游戏多','youxiduojiu3'=>'游戏多业内版','glwzry'=>'攻略');
        return $this->display('btn-download-info', $data);
    }

    public function postEdit()
    {
        $input = Input::only('id', 'gameId','gameName', 'appType', 'appTypeList', 'appVersion','advId','linkType','owmUrl','thirdUrl','mac','idfa','openudid','os','plat','callback','thirdid','gameDownloadId','third-count','third-delids','isAutoLogin');
        //$input['linkValue'] = json_encode(array('owmurl'=>Input::get('owmurl'),'thirdurl'=>Input::get('thirdurl')));
        $data['id'] = $input['id'];
        $data['appType'] = $input['appType'];
        $data['appVersion'] = $input['appVersion'];
        $data['advId'] = $input['advId'];
        $data['gameId'] = $input['gameId'];
        $data['gameName'] = $input['gameName'];
        $data['owmUrl'] = $input['owmUrl'];
        $data['linkType'] = $input['linkType'];
        $data['isAutoLogin'] = $input['isAutoLogin'];
        $data['thirdVendorsList'] = array();
        if($input['linkType'] != 1 && $input['linkType'] != 2){
            unset($data['isAutoLogin']);
        }
        for ($i=0;$i<$input['third-count'];$i++) {
            $thirditem['id'] =  $input['thirdid'][$i];
            $thirditem['gameDownloadId'] =  $input['gameDownloadId'][$i];
            $thirditem['thirdUrl'] =  $input['thirdUrl'][$i];
            $thirditem['mac'] =  $input['mac'][$i];
            $thirditem['idfa'] =  $input['idfa'][$i];
            $thirditem['openudid'] =  $input['openudid'][$i];
            $thirditem['os'] =  $input['os'][$i];
            $thirditem['plat'] =  $input['plat'][$i];
            $thirditem['callback'] =  $input['callback'][$i];
            $data['thirdVendorsList'][] = $thirditem;
        }
        
        if($input['appTypeList']){
            foreach ($input['appTypeList'] as $item) {
                $data['appType'] = $item;
                $result = BtnDownload::save($data);
            }
        } else {
            $result = BtnDownload::save($data);
        }

        if ($input['third-delids']) {
            $delids_arr = explode(',', $input['third-delids']);
            foreach ($delids_arr as $v) {
                $data_d['id'] = $v;
                BtnDownload::del($data_d);
            }
        }
        if ($result) {
            return $this->redirect('v4adv/btndownload/list')->with('global_tips', '保存成功');
        } else {
            return $this->back('保存失败');
        }

    }
    
    public function getDel($id)
    {
        $data_d['id'] = $id;
        $result = BtnDownload::delgamedown($data_d);
        if ($result) {
//             return $this->redirect('v4adv/btndownload/list')->with('global_tips', '删除成功');
            return $this->json(array('state'=>1,'msg'=>'删除成功'));
        } else {
//             return $this->back('删除失败');
            return $this->json(array('state'=>0,'msg'=>'删除失败，请重试'));
        }
    
    }
    
}