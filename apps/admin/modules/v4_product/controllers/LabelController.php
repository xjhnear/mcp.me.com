<?php
/**
 * Created by PhpStorm.
 * User: fujiajun
 * Date: 15/9/21
 * Time: 下午2:02
 */

namespace modules\v4_product\controllers;
use libraries\Helpers;
use Illuminate\Support\Facades\Config;
use Yxd\Modules\Core\SuperController;
use Illuminate\Support\Facades\Input;
use Youxiduo\Helper\MyHelp;
class LabelController extends SuperController
{
    const GENRE = 1;
    const GENRE_STR = 'ios';
    const MALL_MML_API_URL = 'app.mall_mml_api_url';

    /**
     * 初始化
     */
    public function _initialize()
    {
        $this->current_module = 'v4_product';
        $this->controller='label';
        //标签列表
        //http://test.open.youxiduo.com/doc/interface-info/866
        $this->getListurl=Config::get(self::MALL_MML_API_URL).'productTag/get_product_tag_list';
        //添加标签
        //http://test.open.youxiduo.com/doc/interface-info/863
        //http://121.40.78.19:8080/module_mall/productTag/save_product_tag
        $this->getAddurl=Config::get(self::MALL_MML_API_URL).'productTag/save_product_tag';
        //更新标签
        //http://test.open.youxiduo.com/doc/interface-info/864
        //http://121.40.78.19:8080/module_mall/productTag/update_product_tag
        $this->getEditurl=Config::get(self::MALL_MML_API_URL).'productTag/update_product_tag';
        //标签置顶
        //http://test.open.youxiduo.com/doc/interface-info/898
        //http://121.40.78.19:8080/module_mall/productTag/set_product_tag_sort_max
        $this->url['top']=Config::get(self::MALL_MML_API_URL).'productTag/set_product_tag_sort_max';
        $this->url['edit']=Config::get(self::MALL_MML_API_URL).'productTag/update_product_tag';
    }

    protected function _getGlobalData($data=array(),$type='')
    {
        $data['productType']=array('游币商城','钻石商城');
        return $data;
    }

    //弹层查询label
    public function  getLabelListSelect()
    {
        $data = $params = array();
        $inputinfo=Input::all();
        $inputinfo['pageSize']=6;
        if(!empty($inputinfo['page'])) $inputinfo['pageIndex']=!empty($inputinfo['page'])?$inputinfo['page']:1;
        $result=MyHelp::getdata($this->getListurl,$inputinfo);
        if($result['errorCode'] == 0){
            $data = MyHelp::processingInterface($result, $inputinfo, $inputinfo['pageSize']);

            $data['inputinfo']=$inputinfo;
            $html = $this->html('label/pop-label-list',$data);
            return $this->json(array('html'=>$html));
        }
    }
    /**
    public function getLabelListSelect()
    {
        $data = $params = array();
        $inputinfo['tagName']=Input::get('term');
        $result=MyHelp::getdata($this->getListurl,$inputinfo);
        if($result['errorCode'] == 0){
            $result=$result['result'];
            $data=array();
            foreach($result as $key=>$value){
                $data[$key]['id']=$value['tagId'];
                $data[$key]['value']=$value['tagId'].'-'.$value['tagName'];
                $data[$key]['label']=$value['tagName'];
            }
            return $this->json($data);
        }

        $data = array(
        array('id'=>'1','label'=>'网络游戏','value'=>'网络游戏'),
        array('id'=>'2','label'=>'单机游戏','value'=>'单机游戏'),
        array('id'=>'3','label'=>'益智游戏','value'=>'益智游戏'),
        array('id'=>'4','label'=>'竞技体育','value'=>'竞技体育'),
        array('id'=>'5','label'=>'三国','value'=>'三国'),
        array('id'=>'6','label'=>'西游','value'=>'西游'),
        );
        return $this->json($data);

    }
     * ***/




}