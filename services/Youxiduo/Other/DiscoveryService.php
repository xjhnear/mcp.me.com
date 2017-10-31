<?php
namespace Youxiduo\Other;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Youxiduo\Base\BaseService;
use Youxiduo\Bbs\Model\OtherDiscovery;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Config;

class DiscoveryService extends BaseService{

//    const API_URL_CONF = "http://112.124.121.34:28888/module_adapter_other/recData/";
    const API_URL_CONF = 'app.MESSAGE_BASE_URL';
    const API_RELATE_CONF = 'app.game_forum_api_url';
    const API_PHONE_CONF = 'app.android_phone_api_url';
    const API_V4_CONF = 'app.php_v4_module_api_url';


    public static function get_discovery_list($dataType="",$id = "")
    {
        if($dataType){
            $data['dataType'] = $dataType;
        }else{
            $data['dataType'] = 1;
        }

        if($id){
            $data['id'] = $id;
        }
        return Utility::loadByHttp(Config::get(self::API_URL_CONF)."module_adapter_other/recData/"."get_rec_data_info_list",$data,'GET');
    }

    public static function add_discovery($data=array())
    {
        $api_url = "save_rec_data_info";
        //默认值
        $data['dataType'] = 1;
        $data['dataSubType'] = 1;
        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF)."module_adapter_other/recData/".$api_url,$data,'POST');
        return $res;
    }

    public static function edit_discovery($data=array())
    {
        $api_url = "update_rec_data_info";
        //默认值
        if(!isset($data['dataType'])){
            $data['dataType'] = 1;
        }
        $data['dataSubType'] = 1;
        $res = Utility::loadByHttp(Config::get(self::API_URL_CONF)."module_adapter_other/recData/".$api_url,$data,'POST');
        return $res;
    }

    public static function del_discovery($id)
    {
        $data['id'] = $id;
        $data['isActive'] = "false";
        $data['dataType'] = "1";
        return Utility::loadByHttp(Config::get(self::API_URL_CONF)."module_adapter_other/recData/"."update_rec_data_info",$data,'POST');
    }





  }