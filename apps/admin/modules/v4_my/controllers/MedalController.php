<?php
namespace modules\v4_my\controllers;
/**
 * Created by PhpStorm.
 * User: fujiajun
 * Date: 15/11/11
 * Time: 下午3:51
 */

use Illuminate\Support\Facades\Config;
use Youxiduo\MyService\SuperController;
use Input;
use Youxiduo\Helper\MyHelp;
class MedalController extends  SuperController
{

    const API_MODULE_MEDAL_URL = 'app.API_MODULE_MEDAL_URL';

    public function __construct()
    {
        $this->url_array['list_url']=Config::get(self::API_MODULE_MEDAL_URL).'get_medal_info_list';

        $this->current_module = 'v4_my';
        //$this->_config['lookLog']=true;
        parent::__construct($this);
    }

    public function BeforeList($inputinfo)
    {
        $inputinfo['uid']=parent::getSessionUserUid();
        $inputinfo['PHPBst']=1;
        return $inputinfo;
    }

    public function AfterList($result){
        foreach($result['datalist'] as $key=>&$val){
            if(!empty($val['conditions'])){
               list($val['cons'],$val['gameMoney']) =array_values((current(json_decode($val['conditions'],true))));
            }
            if(!empty($val['imgUrl'])){
                $arr=json_decode($val['imgUrl'],true);
                foreach($arr as $key=>$value)
                {
                    if(!empty($value['1'])){
                        $val['list_imgUrl']=MyHelp::getImageUrl($value['1']);
                        break;
                    }
                }
            }

        }
        return $result;
    }


    public function BeforeViewEdit($inputinfo)
    {
        $inputinfo['PHPBst']=1;
        return $inputinfo;
    }

    public function AfterViewEdit($result)
    {
         $result['data']=$result;
         if(!empty($result['data']['conditions'])){
             $result['data']['conditions']=json_decode($result['data']['conditions'],true);
             foreach($result['data']['conditions'] as &$val){
                 $val['cons']=self::set_cons("<input data-rule-digits='true'  data-msg-digits='请填写整数' type='text' name='condition[]' value='".$val['condition']."'>",$result['data']['type']);
             }
         }


        $result['data']['set_cons']=self::set_cons("<input type='text' data-rule-digits='true'  data-msg-digits='请填写整数' name='condition[]' value=''>",$result['data']['type']);
        if(!empty($result['data']['imgUrl'])){
            $arr=json_decode($result['data']['imgUrl'],true);
            foreach($arr as $key=>$value){
                switch(key($value)){
                    case -1: //暗大图标
                        $result['data']['imgUrl_2']=$value['-1'];
                        break;
                    case 1: //亮大图标
                        $result['data']['imgUrl_1']=$value['1'];
                        break;
                    case 0: //亮小图标
                        $result['data']['imgUrl_3']=$value['0'];
                        break;
                    case -2: //暗小图标
                        $result['data']['imgUrl_4']=$value['-2'];
                        break;

                }
            }

        }
        return $result;
    }


    public function BeforePostEdit($inputinfo)
    {
        $this->url_array['post_edit']=Config::get(self::API_MODULE_MEDAL_URL).'update_medal_info';
        if(count($inputinfo['condition']) > 0){
            foreach($inputinfo['condition'] as $key=>$val){
                $data=&$inputinfo['conditions'][];
                $data=array('condition'=>$val,'gameMoney'=>$inputinfo['gameMoney'][$key]);
            }
            $inputinfo['conditions']=json_encode($inputinfo['conditions']);
            unset($inputinfo['condition'],$inputinfo['gameMoney']);
        }
        $inputinfo['imgUrl']=array();
        if(Input::hasFile('imgUrl_2')){
            $inputinfo['imgUrl'][]['-1']=MyHelp::save_img_no_url(Input::file('imgUrl_2'),'medal');
            unset($inputinfo['imgUrl_2']);
        }
        if(Input::hasFile('imgUrl_1')){
            $inputinfo['imgUrl'][]['1']=MyHelp::save_img_no_url(Input::file('imgUrl_1'),'medal');
            unset($inputinfo['imgUrl_1']);
        }
        if(Input::hasFile('imgUrl_3')){
            $inputinfo['imgUrl'][]['00']=MyHelp::save_img_no_url(Input::file('imgUrl_3'),'medal');
            unset($inputinfo['imgUrl_3']);
        }
        if(Input::hasFile('imgUrl_4')){
            $inputinfo['imgUrl'][]['-2']=MyHelp::save_img_no_url(Input::file('imgUrl_4'),'medal');
            unset($inputinfo['imgUrl_4']);
        }
        $inputinfo['imgUrl']=json_encode($inputinfo['imgUrl']);
        return $inputinfo;
    }

    private function set_cons($str,$type){
        switch($type){
            case 'people_admire':
                $str='上周'.$str.'个帖子被设为精华帖';
                break;
            case "quiz_master":
                $str='上周'.$str.'个评论内容被设置为最佳答案';
                break;
            case 'promotion_master':
                $str='上周邀请'.$str.'人来游戏多注册账号';
                break;
            case 'true_tyrant':
                $str='上周兑换商品消耗游币'.$str;
                break;
            case 'save_money':
                $str='上周省钱达到'.$str.'人民币';
                break;
            case 'board_moderator':
                $str="<input type='hidden' name='condition[]' value='任意论坛或版块被设为版主' />任意论坛或版块被设为版主";
                break;

        }
        return $str;
    }


}