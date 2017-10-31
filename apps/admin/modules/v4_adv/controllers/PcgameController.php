<?php
/**
 * Created by PhpStorm.
 * User: fujiajun
 * Date: 15/12/16
 * Time: 上午9:36
 */

namespace modules\v4_adv\controllers;

use Input,Cache;
use Youxiduo\Helper\MyHelp;
use Youxiduo\MyService\QueryService;
use modules\v4_adv\models\Core;
class PcgameController extends \Youxiduo\MyService\SuperController
{
    const  Adv_logo='单机推荐位';//;
    const  Three='';



    public function __construct()
    {
        $this->current_module = "v4_adv";
        $this->_config['databas_table']='yxd_advert.yxd_advert_v4appadv';
        $this->_config['isSql']=true;
        $this->_config['isSqlListen']=true;
        $this->_config['lookLog']=true;

        parent::__construct($this);
    }

    public function BeforeList($inputinfo)
    {
        $inputinfo['where']=$inputinfo;
        $inputinfo['sqlWhere']=array('startTime'=>' startTime > ? ','endTime'=>' startTime < ? ','name'=>" name like ? ");
        $inputinfo['orderby']=' ORDER BY  event_id DESC ';
        $inputinfo['list']=1;//这步必须放在最后
        $inputinfo['adv_logo']=self::Adv_logo;
        return $inputinfo;
    }

    public function AfterList($data)
    {
        $data['datalist']=MyHelp::getImgUrlforlist($data['datalist'],'adv_img');
        return $data;
    }

    /**
     * @return bool|mixed|string
     */
    public function AfterViewAdd($inputinfo)
    {
        $inputinfo['type']=MyHelp::getAdv_Type();
        $inputinfo['sort']=array(
            '1'=>'单机推荐位1',
            '2'=>'单机推荐位2',
            '3'=>'单机推荐位3',
            '4'=>'单机推荐位4',
            '5'=>'单机推荐位5'
        );
        return $inputinfo;
    }

    public function BeforePostAdd($inputinfo)
    {
        Cache::forget('list_'.$this->_config['controller'].'_'.Input::get('page',1));
        $inputinfo=self::getInfo($inputinfo);
        $inputinfo['createuser']=parent::getSessionUserUid().'-'.parent::getSessionUserName();
        $inputinfo['createdate']=$this->_config['date'];
        return $inputinfo;
    }

    public function AfterViewEdit($result)
    {

        $result['data']=$result;
        if(!empty($result['thirdParty'])){
            $result['data']+=json_decode($result['data']['thirdParty'],true);
        }
        $result['type']=MyHelp::getAdv_Type();
        $result['sort']=array(
            '1'=>'单机推荐位1',
            '2'=>'单机推荐位2',
            '3'=>'单机推荐位3',
            '4'=>'单机推荐位4',
            '5'=>'单机推荐位5'
        );
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
        Cache::forget('list_'.$this->_config['controller'].'_'.Input::get('page',1));
        $inputinfo=self::getInfo($inputinfo);
        $inputinfo['modifyuser']=parent::getSessionUserUid().'-'.parent::getSessionUserName();
        return $inputinfo;
    }

    public function getIsshow($id,$isShow,$attr,$date='')
    {
        if($date < date('Y-m-d H:i:s',time()) and $isShow == 1){
            return $this->back()->withInput()->with('global_tips','此广告结束时间已经过期');
        }
        $where=array('id',$id);
        $inputinfo['attribute']=$attr;
        //$inputinfo['is_show']=$isShow;
        QueryService::$databas_table=$this->_config['databas_table'];
        $result=QueryService::editData($inputinfo,$where);
        if($result['errorCode']==0){
            $data_del_cache = Core::delcache(array('type'=>1));
            if (isset($data_del_cache)&&$data_del_cache == false) {
                return $this->redirect(str_replace('_','',$this->current_module).'/'.strtolower($this->_config['controller']).'/list')->with('global_tips','操作成功,缓存失败');
            }
            return $this->redirect(str_replace('_','',$this->current_module).'/'.strtolower($this->_config['controller']).'/list')->with('global_tips','操作成功');
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

    private function getInfo($inputinfo){
        if(Input::hasFile('adv_img')){
           if(isset($inputinfo['img_icon'])){
               unset($inputinfo['img_icon']);
               $inputinfo['adv_img']=MyHelp::save_img_no_url(Input::file('adv_img'));
            }else{
                $inputinfo['adv_img']=MyHelp::save_img_no_url(Input::file('adv_img'));
            }
        }else{
            if(isset($inputinfo['img_icon'])){
                 unset($inputinfo['adv_img']);
                 $input=Input::all();
                 $inputinfo['adv_img']=str_replace('http://test.img.youxiduo.com','',$input['img_icon']);
                 unset($inputinfo['img_icon']);
            }else{
                 unset($inputinfo['adv_img']);
            }
        }
        if (!$inputinfo['startTime']) $inputinfo['startTime'] = date("Y-m-d 00:00:00");
        if (!$inputinfo['endTime']) $inputinfo['endTime'] = date("Y-m-d 00:00:00",strtotime("+10 year"));
        $inputinfo['adv_logo']=self::Adv_logo;
        if (isset($inputinfo['game_id'])) {
            $inputinfo['urlId'] = $inputinfo['game_id'];
        }
        $keys=array_flip(explode(',',self::Three));
        return MyHelp::set_adv_inputinfo($inputinfo,$this->_config['date'],$keys);
    }
}