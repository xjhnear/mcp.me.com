<?php
/**
 * Created by PhpStorm.
 * User: fujiajun
 * Date: 15/10/22
 * Time: 上午11:37
 */

namespace modules\v4_product\controllers;

use libraries\Helpers;
use Youxiduo\Helper\Utility;
use Youxiduo\V4\Game\GameService;
use Youxiduo\Imall\ProductService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\DES;
use Youxiduo\V4\User\UserService;

class FormController  extends BackendController
{

    public function _initialize()
    {
        $this->current_module = 'v4_product';
    }


    /**
     * @return mixed
     */
    public function  getList()
    {
        $data = $params = array();
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =10;
        if(Input::get("templateName")){
            $params['templateName']=Input::get("templateName");
        }
        $result=ProductService::getFormList($params);
        if($result['errorCode']==0){
            $data=self::processingInterface($result,$params);
            $data['inputinfo']=$params;
            return $this->display('form/form-list',$data);
        }
        self::error_html($result);
    }

    /***
    [detailId] =&gt; 1
    [templateId] =&gt; 1
    [detailKey] =&gt; 电话
    [sort] =&gt; 1
    [notNull] =&gt;
    [needEncrypt] =&gt;
    [isActive] =&gt; 1
     ***/
    public function getAddEditForm($id=0,$name='')
    {
        $data=array();$data['count']=0;
        if(empty($id)) return $this->display('form/add-edit-form',$data);
        $uid=$this->getSessionData('youxiduo_admin');
        if(empty($uid['id'])) return $this->redirect('v4product/form/list')->with('global_tips','用户名获取失败');
        $data=ProductService::getTemplate(array('templateId'=>$id));
        if($data['errorCode']==0){
            $sort=end($data['result']);
            return $this->display('form/add-edit-form',array('info'=>$data['result'],'count'=>!empty($sort['sort'])?$sort['sort']:0,'templateName'=>$name,'templateId'=>$id));
        }
        return $this->redirect('v4product/form/list')->with('global_tips','编辑接口调用失败。');
    }

    public function postFormSave()
    {
        $input=Input::only('detailValue','templateId','sort','detailId','templateName','input_name','count','ids_del');
        $params['templateName']=$input['templateName'];
        $size=sizeof($input['input_name']);

        for($i=0;$i<$size;$i++){
            if(!empty($input['input_name'][$i])){
                $params['templateDetailList'][$i]=array(
                    'detailKey'=>$input['input_name'][$i],
                    'sort'=>intval($input['sort'][$i]),
                    'detailValue'=>!empty($input['detailValue'][$i])?$input['detailValue'][$i]:'',
                );
                if(!empty($input['detailId'][$i])){
                    $params['templateDetailList'][$i]['detailId']=$input['detailId'][$i];
                }

            }else{
                return $this->back()->with('global_tips','操作失败');
            }
        }
        $ids_del_arr = explode(',',$input['ids_del']);
        foreach($ids_del_arr as $v){
            if($v){
                $params['templateDetailList'][] = array(
                    'detailId' => $v,
                    'isActive' => "false"
                );
            }
        }
        if(!empty($input['templateId'])){
            $params['templateId']=$input['templateId'];
            $result=ProductService::modify_form($params);
        }else{
            $result=ProductService::add_form($params);
        }
        ///product/modify_form
        if($result['errorCode']==0)
        {
            return $this->redirect('v4product/form/list')->with('global_tips','操作成功');
        }
    }

    public function getDeleteForm($id=0)
    {
        if(empty($id)){
            return $this->back()->with('global_tips','操作失败-ID缺失');
        }
        $result=ProductService::deleteForm(array('templateId'=>$id));
        if($result['errorCode']==0){
            return $this->redirect('v4product/form/list')->with('global_tips','操作成功');
        }
        return $this->back()->with('global_tips','操作失败');
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

}