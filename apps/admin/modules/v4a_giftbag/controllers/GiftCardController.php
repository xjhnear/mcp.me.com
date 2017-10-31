<?php
/**
 * Created by PhpStorm.
 * User: fujiajun
 * Date: 15/11/17
 * Time: 下午2:11
 */

namespace modules\v4a_giftbag\controllers;
use Youxiduo\Helper\MyHelp;
use Youxiduo\MyService\SuperController;
use Input,Config;
use Youxiduo\Helper\DES;
class GiftcardController extends SuperController
{

    const ios_virtual_card_url = 'app.ios_virtual_card_url';

    public function __construct()
    {
        $this->current_module = 'v4a_giftbag';
        /**初始化**/
        $this->url_array['list_url']=Config::get('app.ios_virtual_card_url').'virtualcard/info_list';
        //$this->_config['lookLog']=true;
        $this->_config['isFirePHP']=true;
        parent::__construct($this);

    }

    protected function BeforeList($inputinfo)
    {
        if(!empty($inputinfo['cardCode'])){
            $inputinfo['cardCode']=$inputinfo['cardCode'];
        }else{
            $inputinfo['cardCode']=$inputinfo['restId'];
            unset($inputinfo['restId']);
        }

        $inputinfo['pageSize']=15;
        return $inputinfo;
    }

    public function getDownload()
    {
        $inputinfo=http_build_query(Input::all());
        $data['url']=Config::get('app.ios_virtual_card_url').'virtualcard/exportInfo';
        if(!empty($inputinfo)){
            $data['url']=$data['url'].'?'.$inputinfo;
        }
        $str=date("YmdHis").'礼包卡数据提取.xls';
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=$str");
        readfile($data['url']);
        exit;
    }

    protected function AfterList($data){

        foreach($data['datalist'] as $key=>&$val) {
            $data['userinfo'] = MyHelp::getUser($data['datalist'], 'usedBy');
            $val['cardInfo']=!empty($val['cardInfo'])?DES::decrypt($val['cardInfo'],11111111):'没有数据';
        }
        $data['cardStatusList']=array(''=>'','0'=>'未领取','2'=>'已领取');
        return $data;
    }


    /***
    public function getList($id=0)
    {   $input=Input::all();
        $input['cardCode']=$id;

        $data=$this->service->_ListByView($input);
        foreach($data['datalist'] as $key=>&$val){
            $val['cardInfo']=!empty($val['cardInfo'])?DES::decrypt($val['cardInfo'],11111111):'';
        }

        $data['userinfo']=MyHelp::getUser($data['datalist'],'usedBy');
        return $this->display('giftcard/giftcard-list',$data);
    }
    ***/



    public function getDelect($id=0)
    {
        $inputinfo['cardinfoId']=$id;
        $result=MyHelp::curldata(Config::get('app.ios_virtual_card_url').'virtualcard/delete',$inputinfo,'GET',$this->_config['lookLog']);
        if($result['errorCode'] != 0){
            return $this->back()->with('global_tips',$result['errorDescription']);
        }
        return $this->back()->with('global_tips','删除成功');
    }


}