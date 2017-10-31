<?php

namespace modules\v4_scheme\controllers;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\MyHelp;
use libraries\Helpers;
use Youxiduo\Helper\Utility;
use Youxiduo\V4\Game\GameService;
use Youxiduo\V4\Game\SchemeService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Youxiduo\Cache\CacheService;
use Youxiduo\Helper\DES;
use Youxiduo\V4\User\UserService;
use modules\v4user\models\UserModel;

/*
    fujiajun 4.0 后台商城 2015/3/2
*/

class RecordController extends BackendController
{
    const GENRE = 1;
    const GENRE_STR = 'ios';

    public function _initialize()
    {
        $this->current_module = 'v4_scheme';
    }

    /**视图：商品管理列表**/
    public function getList()
    {
        $params = $data = array();
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =10;
        $params['active'] ='true';
        $arr_=array('gid','schemeKey');
        foreach($arr_ as $v){
            if(Input::get($v)){
                $params[$v]=Input::get($v);
            }
        }

        $result=SchemeService::searchRecordList($params);
//         print_r($result);exit;
        if($result['errorCode']==0){
            $data=self::processingInterface($result,$params);
            return $this->display('record-list',$data);
        }
        return $this->display('record-list',$data);
    }
    
    public function postList()
    {
        $params = $data = array();
        $export = (int)Input::get('export',0);
        
        $arr_=array('gid','schemeKey');
        foreach($arr_ as $v){
            if(Input::get($v)){
                $params[$v]=Input::get($v);
            }
        }

        if($export==0){
            $params['active'] = 'false';
            $result = SchemeService::updateRecord($params);
            unset($params['active']);
        }
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =10;
        $params['active'] ='true';
        $result=SchemeService::searchRecordList($params);
        //         print_r($result);exit;
        if($result['errorCode']==0){
            $data=self::processingInterface($result,$params);
            return $this->display('record-list',$data);
        }
        return $this->display('record-list',$data);
    }
    

    public function getAjaxDel($id=''){
        if(!$id) return $this->json(array('state'=>0,'msg'=>'数据错误'));
        $result = SchemeService::updateRecord(array('id'=>$id,'active'=>'false'));
        if(!$result['errorCode']){
            return $this->json(array('state'=>1,'msg'=>'删除成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'删除失败，请重试'));
        }
    }
    

    /**处理接口返回数据**/
    private static function processingInterface($result,$data,$pagesize=10){ //echo $result['totalCount'];exit;
        $pager = Paginator::make(array(),!empty($result['totalCount'])?$result['totalCount']:0,$pagesize);
        //print_r($pager);
        unset($data['pageIndex']);
        $pager->appends($data);

        $data['pagelinks'] = $pager->links();
        $data['datalist'] = !empty($result['result'])?$result['result']:array();
        return $data;
    }

    /**错误输出 **/
    private function errorHtml($result=array(),$str=''){
        header("Content-type: text/html; charset=utf-8");
        return $this->back()->with('global_tips',$str.'出错拉');
        exit;
    }

}