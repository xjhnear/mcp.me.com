<?php
namespace modules\v4system\models;

use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Config as baseConfig;

class Report extends BaseHttp
{
    /**
     * @param $dealType
     * @param $informerName
     * @param $infomantName
     * @param int $pageIndex
     * @param int $pageSize
     * @return array
     */
    public static function search($dealType,$informerName,$infomantName,$pageIndex=1,$pageSize=10)
    {
        $apiurl = baseConfig::get(self::HOST_URL) . 'module_adapter_other/report/get_report_info_list';
        $params = array();
        if($dealType==0){

        }elseif($dealType==1){
            $params['dealType'] = 0;
        }elseif($dealType==2){
            $params['isDeal'] = 'true';
        }
        $informerName && $params['informerName'] = $informerName;
        $infomantName && $params['infomantName'] = $infomantName;
        $params['pageIndex'] = $pageIndex;
        $params['pageSize'] = $pageSize;
        $result = self::http($apiurl,$params);
        //var_dump($result);exit;
        if($result['errorCode']==0){
            if(is_array($result['result'])){
                foreach($result['result'] as $key=>$row){
                    $row['infomantDesc'] = json_decode($row['infomantDesc'],true);
                    if(isset($row['infomantDesc']['imgId'])){
                        $row['infomantDesc']['imgId'] = Utility::getImageUrl($row['infomantDesc']['imgId']);
                    }
                    $result['result'][$key] = $row;
                }
            }
            return array('result'=>$result['result'],'totalCount'=>$result['totalCount']);
        }

        return array('result'=>array(),'totalCount'=>0);
    }

    /**
     * @param $dealType
     * @param $id
     * @return bool
     */
    public static function doInform($dealType,$id)
    {
        $apiurl = baseConfig::get(self::HOST_URL) . 'module_adapter_other/report/update_report_info';
        $params = array('dealType'=>$dealType,'id'=>$id);
        $result = self::http($apiurl,$params,'POST','json');
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }

}