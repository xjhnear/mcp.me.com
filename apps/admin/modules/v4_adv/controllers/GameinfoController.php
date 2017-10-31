<?php
/**
 * Created by PhpStorm.
 * User: fujiajun
 * Date: 15/12/16
 * Time: 下午12:00
 */

namespace modules\v4_adv\controllers;
use Input,Cache;
use Youxiduo\Helper\MyHelp;
use Youxiduo\MyService\QueryService;
use Youxiduo\Adv\AdvService;
use modules\v4_adv\models\Core;

class GameinfoController extends \Youxiduo\MyService\SuperController
{
    const  Adv_logo='游戏详情';//;
    const  Three='searchName,address,mac,idfa,openudid,os,plat,callback,thirdid';


    public function __construct()
    {
        $this->current_module = "v4_adv";
        $this->_config['databas_table']='yxd_advert.yxd_advert_v4appadv';
        $this->_config['isSql']=true;
        $this->_config['except']=array('third-count','third-delids','thirdVendorsList');
        //$this->_config['isSqlListen']=true;

        //$this->_config['lookLog']=true;
        parent::__construct($this);
    }

    public function BeforeList($inputinfo)
    {
        $inputinfo['where']=$inputinfo;
        $inputinfo['sqlWhere']=array('startTime'=>' startTime > ? ','endTime'=>' startTime < ? ','name'=>" name like ? ",'platform'=>" platform = ? ");
        //$inputinfo['sqlWhere']=array('name'=>' name like ? ');
        $inputinfo['orderby']='  ORDER BY event_id ASC ';
        $inputinfo['list']=1;//这步必须放在最后
        //$inputinfo['setWhere']=' and  is_show=1 ';
        $inputinfo['adv_logo']=self::Adv_logo;
        return $inputinfo;
    }

    public function AfterList($data)
    {
        $data['platformlist']=array('ios'=>'游戏多IOS','iosyn'=>'游戏多IOS业内版');
        return $data;
    }
    
    public function BeforePostAdd($inputinfo)
    {
        $inputinfo=self::getInfo($inputinfo);
        $inputinfo['createuser']=parent::getSessionUserUid().'-'.parent::getSessionUserName();
        $inputinfo['createdate']=$this->_config['date'];
        return $inputinfo;
    }

    public function AfterViewEdit($result)
    {

        $result['data']=$result;
            $third=AdvService::FindAdvthird(' advid="'.$result['data']['id'].'"');
        $result['data']['thirdVendorsList'] = array();
        $result['data']['thirdcount'] = count($third);
        foreach ($third as $item) {
            $result['data']['thirdVendorsList'][] = $item;
        }
//         if(!empty($result['thirdParty'])){
//             $result['data']+=json_decode($result['data']['thirdParty'],true);
//         }
        $result['type']=MyHelp::getAdv_Type();
        $result['data']['adv_img']=MyHelp::getImageUrl($result['data']['adv_img'],1);
        return $result;
    }

    public function BeforeViewEdit($inputinfo)
    {
        $inputinfo['where']=$inputinfo;
        $inputinfo['sqlWhere']=array('id'=>' id = ? ');
        $inputinfo['limit']=' LIMIT 1 ';
        return $inputinfo;
    }

    public function BeforePostEdit($inputinfo)
    {

        $inputinfo=self::getInfo($inputinfo);
        $inputinfo['modifyuser']=parent::getSessionUserUid().'-'.parent::getSessionUserName();

        return $inputinfo;
    }

    public function getIsshow($id,$isShow,$attr,$date='')
    {
        Cache::forget('list_'.$this->_config['controller'].'_'.Input::get('page',1));
        $where=array('id',$id);
        $inputinfo['attribute']=$attr;
        //$inputinfo['is_show']=$isShow;
        QueryService::$databas_table=$this->_config['databas_table'];
        $result=QueryService::editData($inputinfo,$where);
        if($result['errorCode']==0){
            $data_del_cache = Core::delcache(array('type'=>1));
            if (isset($data_del_cache)&&$data_del_cache == false) {
                return $this->redirect(str_replace('_','',$this->current_module).'/'.$this->_config['controller'].'/list')->with('global_tips','操作成功,缓存失败');
            }
            return $this->redirect(str_replace('_','',$this->current_module).'/'.$this->_config['controller'].'/list')->with('global_tips','操作成功');
        }else{
            return $this->back()->withInput()->with('global_tips',$result['errorDescription']);
        }
    }

    //删除
    public function getOff($id)
    {
        $where[]=$id;
        QueryService::$databas_table=$this->_config['databas_table'];
        $result=QueryService::delectData($where);
        if($result==1){
            AdvService::AdvthirdDel($id,'advid');
            $data_del_cache = Core::delcache(array('type'=>1));
            if (isset($data_del_cache)&&$data_del_cache == false) {
//                 return $this->redirect(str_replace('_','',$this->current_module).'/'.$this->_config['controller'].'/list')->with('global_tips','修改成功,缓存失败');
                return $this->json(array('state'=>1,'msg'=>'删除成功'));
            }
//             return $this->redirect(str_replace('_','',$this->current_module).'/'.$this->_config['controller'].'/list')->with('global_tips','修改成功');
            return $this->json(array('state'=>1,'msg'=>'删除成功'));
        }else{
//             return $this->back()->withInput()->with('global_tips',$result['errorDescription']);
            return $this->json(array('state'=>0,'msg'=>'删除失败，请重试'));
        }
    }

    /**
     * @return bool|mixed|string
     */
    public function AfterViewAdd($inputinfo)
    {
        $inputinfo['type']=MyHelp::getAdv_Type();
        return $inputinfo;
    }


    private function getInfo($inputinfo){

        if(Input::hasFile('adv_img')){
            $inputinfo['adv_img']=MyHelp::save_img_no_url(Input::file('adv_img'));
        }else{
            unset($inputinfo['adv_img']);
        }
        if (!$inputinfo['startTime']) $inputinfo['startTime'] = date("Y-m-d 00:00:00");
        if (!$inputinfo['endTime']) $inputinfo['endTime'] = date("Y-m-d 00:00:00",strtotime("+10 year"));
        for ($i=0;$i<$inputinfo['third-count'];$i++) {
            $thirditem['id'] =  $inputinfo['thirdid'][$i];
            $thirditem['address'] =  $inputinfo['address'][$i];
            $thirditem['mac'] =  $inputinfo['mac'][$i];
            $thirditem['idfa'] =  $inputinfo['idfa'][$i];
            $thirditem['openudid'] =  $inputinfo['openudid'][$i];
            $thirditem['os'] =  $inputinfo['os'][$i];
            $thirditem['plat'] =  $inputinfo['plat'][$i];
            $thirditem['callback'] =  $inputinfo['callback'][$i];
            $inputinfo['thirdVendorsList'][] = $thirditem;
        }
        $inputinfo['adv_logo']=self::Adv_logo;
        $keys=array_flip(explode(',',self::Three));
        return MyHelp::set_adv_inputinfo($inputinfo,$this->_config['date'],$keys);
    }

    public function getAutosearch()
    {
        $key=Input::all();
        $arr['results']=MyHelp::getAutoSearch($key['key'],$key['variableName']);
        echo json_encode($arr);
        exit;
    }
    
    public function AfterPostAdd($result)
    {
        if (isset($result['inputinfo']['thirdVendorsList'])) {
            foreach ($result['inputinfo']['thirdVendorsList'] as $item) {
                $item['advid'] = $result['result'];
                AdvService::AdvthirdSave($item);
            }
        }
        if (isset($result['inputinfo']['third-delids'])) {
            $delids_arr = explode(',', $result['inputinfo']['third-delids']);
            foreach ($delids_arr as $v) {
                AdvService::AdvthirdDel($v);
            }
        }
    }
    
    public function AfterPostEdit($inputinfo)
    {
        if (isset($inputinfo['thirdVendorsList'])) {
            foreach ($inputinfo['thirdVendorsList'] as $item) {
                $item['advid'] = $inputinfo['id'];
                AdvService::AdvthirdSave($item);
            }
        }
        if (isset($inputinfo['third-delids'])) {
            $delids_arr = explode(',', $inputinfo['third-delids']);
            foreach ($delids_arr as $v) {
                AdvService::AdvthirdDel($v);
            }
        }
    }
}