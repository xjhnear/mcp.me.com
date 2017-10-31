<?php
/**
 * Created by PhpStorm.
 * User: fujiajun
 * Date: 16/1/13
 * Time: 上午10:31
 */

namespace modules\v4_adv\controllers;



use Youxiduo\MyService\SuperController;
use Youxiduo\MyService\QueryService;
use Youxiduo\Helper\MyHelp;
class GuessyoulikeController extends SuperController
{
    const  Adv_logo='猜你喜欢';//;
    const  Three='';

    public function __construct()
    {
        $this->current_module = "v4_adv";
        $this->_config['databas_table']='yxd_advert.yxd_advert_v4appadv';
        $this->_config['isSql']=true;
        //$this->_config['isSqlListen']=true;
        //$this->_config['lookLog']=true;

        parent::__construct($this);
    }

    public function BeforeList($inputinfo)
    {
        $inputinfo['where']=$inputinfo;
        $inputinfo['sqlWhere']=array('name'=>' name like ? ');
        $inputinfo['orderby']=' ORDER BY  event_id DESC ';
        $inputinfo['list']=1;//这步必须放在最后
        $inputinfo['adv_logo']=self::Adv_logo;
        return $inputinfo;
    }

    public function BeforeViewEdit($inputinfo)
    {
        $inputinfo['where']=$inputinfo;
        $inputinfo['sqlWhere']=array('id'=>' id = ? ');
        $inputinfo['limit']=' LIMIT 1 ';
        return $inputinfo;
    }

    public function AfterViewEdit($result)
    {

        $result['data']=$result;
        if(!empty($result['thirdParty'])){
            $result['data']+=json_decode($result['data']['thirdParty'],true);
        }
        return $result;
    }


    public function getIsshow($id,$isShow,$attr,$date='')
    {
        if($date < date('Y-m-d H:i:s',time()) and $isShow == 1){
            return $this->back()->withInput()->with('global_tips','此广告结束时间已经过期');
        }
        $where=array('id',$id);
        $inputinfo['attribute']=intval($attr);
        //$inputinfo['is_show']=intval($isShow);
        QueryService::$databas_table=$this->_config['databas_table'];
        $result=QueryService::editData($inputinfo,$where);
        if($result['errorCode']==0){
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

    public function BeforePostAdd($inputinfo)
    {
        $inputinfo=self::getInfo($inputinfo);
        $inputinfo['createuser']=parent::getSessionUserUid().'-'.parent::getSessionUserName();
        $inputinfo['createdate']=$this->_config['date'];
        return $inputinfo;
    }

    public function BeforePostEdit($inputinfo)
    {
        $inputinfo=self::getInfo($inputinfo);
        $inputinfo['modifyuser']=parent::getSessionUserUid().'-'.parent::getSessionUserName();
        return $inputinfo;
    }

    private function getInfo($inputinfo){

        $inputinfo['adv_logo']=self::Adv_logo;
        $keys=array_flip(explode(',',self::Three));
        if (isset($inputinfo['game_id'])) {
            $inputinfo['urlId'] = $inputinfo['game_id'];
        }
        return MyHelp::set_adv_inputinfo($inputinfo,$this->_config['date'],$keys);
    }
}