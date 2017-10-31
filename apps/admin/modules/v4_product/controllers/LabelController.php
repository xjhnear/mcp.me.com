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

use Illuminate\Support\Facades\Input;
use Youxiduo\Helper\MyHelp;
use Youxiduo\MyService\SuperController;

class LabelController extends SuperController
{
    const GENRE = 1;
    const GENRE_STR = 'ios';
    const MALL_MML_API_URL = 'app.mall_mml_api_url';

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->current_module = 'v4_product';
        //标签列表
        //http://test.open.youxiduo.com/doc/interface-info/866
        $this->url_array['list_url']=Config::get(self::MALL_MML_API_URL).'productTag/get_product_tag_list';
        //添加标签
        //http://test.open.youxiduo.com/doc/interface-info/863
        //http://121.40.78.19:8080/module_mall/productTag/save_product_tag
        $this->url_array['post_add']=Config::get(self::MALL_MML_API_URL).'productTag/save_product_tag';
        //更新标签
        //http://test.open.youxiduo.com/doc/interface-info/864
        //http://121.40.78.19:8080/module_mall/productTag/update_product_tag
        $this->url_array['post_edit']=Config::get(self::MALL_MML_API_URL).'productTag/update_product_tag';
        //标签置顶
        //http://test.open.youxiduo.com/doc/interface-info/898
        //http://121.40.78.19:8080/module_mall/productTag/set_product_tag_sort_max
        $this->url_array['set']['top']=Config::get(self::MALL_MML_API_URL).'productTag/set_product_tag_sort_max';
        $this->url_array['set']['edit']=Config::get(self::MALL_MML_API_URL).'productTag/update_product_tag';
        parent::__construct($this);
    }

    protected function AfterViewAdd($data)
    {
        $data['productType']=array('0'=>'游币商城','1'=>'钻石商城');
        return $data;
    }


    protected function AfterViewEdit($data){
        $data['data']=$data;
        $data['productType']=array('0'=>'游币商城','1'=>'钻石商城');
        return $data;
    }

    //弹层查询label
    public function  getLabelListSelect($selected_categoryId='')
    {
        $inputinfo=Input::all();
        $inputinfo['pageSize']=6;
        if(!empty($inputinfo['page'])) $inputinfo['pageIndex']=!empty($inputinfo['page'])?$inputinfo['page']:1;
        if(!empty($selected_categoryId))
            $inputinfo['categoryId']=$selected_categoryId;


        $result=MyHelp::curldata($this->url_array['list_url'],$inputinfo,'GET');
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