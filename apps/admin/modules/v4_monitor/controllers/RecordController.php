<?php

namespace modules\v4_monitor\controllers;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\MyHelp;
use libraries\Helpers;
use Youxiduo\Helper\Utility;
use Youxiduo\Monitor\RecordService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Youxiduo\Cache\CacheService;

/*
    fujiajun 4.0 后台商城 2015/3/2
*/

class RecordController extends BackendController
{
    const GENRE = 1;
    const GENRE_STR = 'ios';

    public function _initialize()
    {
        $this->current_module = 'v4_monitor';
    }

    /**视图：主线程管理列表**/
    public function getList()
    {
        $params = $data = array();
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =10;
        
        $arr_=array('baseId','addtime','urlName');
        foreach($arr_ as $v){
            if(Input::get($v)){
                $params[$v]=Input::get($v);
            }
        }

        $result=RecordService::searchRecordList($params);
//         print_r($result);exit;
        if($result['errorCode']==0){
            foreach ($result['result'] as &$item) {
                if(isset($item['urlDesc']) && $item['urlDesc']){
                    $item = array_merge($item,json_decode($item['urlDesc'],true));
                }
            }
//             print_r($result);exit;
            $data=self::processingInterface($result,$params);
            $data['totalcount'] = $result['totalCount'];
            return $this->display('record-list',$data);
        }
        return $this->display('record-list',$data);
    }
    
    public function getDetail($baseId=''){
        if(!$baseId) return $this->back('数据错误');
        $pro_res = RecordService::searchRecordList(array('baseId'=>$baseId));
        if($pro_res['errorCode'] || !$pro_res['result']) return $this->back('无效数据');
        $pro_info = $pro_res['result'][0];
        if(isset($pro_info['urlDesc']) && $pro_info['urlDesc']){
            $pro_info = array_merge($pro_info,json_decode($pro_info['urlDesc'],true));
        }
        foreach ($pro_info['processList'] as &$item) {
            if(isset($item['receiveData']) && $item['receiveData']){
                $item = array_merge($item,json_decode($item['receiveData'],true));
            }
        }
        return $this->display('detail',array('info'=>$pro_info));
    }
    
    /**视图：子接口管理列表**/
    public function getProcessList()
    {
        $params = $data = array();
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =10;
    
        $arr_=array('baseId','addtime','processName');
        foreach($arr_ as $v){
            if(Input::get($v)){
                $params[$v]=Input::get($v);
            }
        }
    
        $result=RecordService::searchProcessList($params);
//         print_r($result);exit;
        if($result['errorCode']==0){
            foreach ($result['result'] as &$item) {
                if(isset($item['receiveData']) && $item['receiveData']){
                    $item = array_merge($item,json_decode($item['receiveData'],true));
                }
            }
            //             print_r($result);exit;
            $data=self::processingInterface($result,$params);
            $data['totalcount'] = $result['totalCount'];
            return $this->display('process-list',$data);
        }
        return $this->display('process-list',$data);
    }

    public function getDelete(){
        return $this->display('delete');
    }
    
    public function postDelete(){
        $input = Input::all();
        if(!$input['addtime']) return $this->back('删除失败');
        $result=RecordService::Delete(array('addtime'=>$input['addtime']));
        if($result['errorCode']==0){
            return $this->redirect('v4monitor/record/delete')->with('global_tips','删除成功');
        }else{
            return $this->back()->withInput()->with('global_tips','删除失败');
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