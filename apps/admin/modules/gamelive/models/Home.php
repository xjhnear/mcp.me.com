<?php
namespace modules\gamelive\models;

class Home extends BaseHttp
{
    /**
     * @return array
     */
    public static function GetIndexHeaderVideo()
    {
        $url = self::HOST_URL .'GetIndexHeaderVideo';
        $params = array();
        $result = self::http($url,$params);
        if ($result !== false && $result['errorCode'] == 0) {
            return $result['result'];
        }
        return array();
    }

    /**
     * @param $idx
     * @param $picUrl
     * @param $summary
     * @param $videoUrl
     * @param $redirecturl
     * @param $videoLive
     * @param $picMin
     * @return bool
     */
    public static function SaveIndexHeaderVideo($idx,$picUrl,$summary,$videoUrl,$redirecturl,$videoLive,$picMin,$videoMobileUrl,$liveMobileUrl)
    {
        $url = self::HOST_URL . 'SaveIndexHeaderVideo';
        $params = array(
            'idx'=>$idx,
            'picUrl'=>$picUrl,
            'summary'=>$summary,
            'videoUrl'=>$videoUrl,
            'videoLive'=>$videoLive,
            'picAtt'=>$redirecturl,
            'picMin'=>$picMin,
            'videoMobileUrl'=>$videoMobileUrl,
            'liveMobileUrl'=>$liveMobileUrl
        );
        //print_r($params);exit;
        $result = self::http($url,$params);
        if ($result !== false && $result['errorCode'] == 0) {
            return true;
        }
        //var_dump($result);exit;
        return false;
    }

    /**
     * @param $idx
     * @return bool
     */
    public static function RemoveIndexHeaderVideo($idx)
    {
        $url = self::HOST_URL . 'RemoveIndexHeaderConfig';
        $params = array('idx'=>$idx);
        $result = self::http($url,$params,'POST');
        if ($result !== false && $result['errorCode'] == 0) {
            return true;
        }
        //var_dump($result);exit;
        return false;

    }

    /**
     * @return array
     */
    public static function GetIndexWeekShow()
    {
        $url = self::HOST_URL . 'GetIndexWeekShow';
        $params = array();
        $result = self::http($url,$params);
        if ($result !== false && $result['errorCode'] == 0) {
            return $result['result'];
        }
        return array();
    }

    /**
     * @param $day
     * @param $result
     * @param $idx
     * @return bool
     */
    public static function SaveIndexWeekShow($day,$result,$idx)
    {
        $url = self::HOST_URL . 'SaveIndexWeekShow';
        $params = array(
            'day'=>$day,
            'result'=>json_encode(array('result'=>$result),JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
            'idx'=>$idx
        );
        $result = self::http($url,$params,'POST');
        if ($result !== false && $result['errorCode'] == 0) {
            return true;
        }
        var_dump($result);exit;
        return false;
    }
    public static function SaveIndexDown($data){
        $url = self::HOST_URL . 'SaveIndexDown';
        $res = self::http($url,$data,'POST');
        if(!$res)   return array('success'=>false,'error'=>"接口不存在或参数错误",'data'=>false);
        if(!$res['errorCode'])  return array('success' => true, 'error' => false, 'data' => false);
        return array('success'=>false,'error'=>$res['errorDescription'],'data'=>false);
    }
    public static function GetIndexDown($data){
        $url = self::HOST_URL . 'GetIndexDown';
        $res = self::http($url,$data,'POST');
        if(!$res)   return array('success'=>false,'error'=>"接口不存在或参数错误",'data'=>false);
        if(!$res['errorCode'])  return array('success' => true, 'error' => false, 'data' => $res['result']);
        return array('success'=>false,'error'=>$res['errorDescription'],'data'=>false);
    }
    public static function RemoveIndexDown($data){
        $url = self::HOST_URL . 'RemoveIndexDown';
        $res = self::http($url,$data,'POST');
        if(!$res)   return array('success'=>false,'error'=>"接口不存在或参数错误",'data'=>false);
        if(!$res['errorCode'])  return array('success' => true, 'error' => false, 'data' => false);
        return array('success'=>false,'error'=>$res['errorDescription'],'data'=>false);
    }

    public static function SaveIndexGuangGaoCS($data){
        $url = self::HOST_URL . 'SaveIndexGuangGaoCS';
        $res = self::http($url,$data,'POST');
        if(!$res)   return array('success'=>false,'error'=>"接口不存在或参数错误",'data'=>false);
        if(!$res['errorCode'])  return array('success' => true, 'error' => false, 'data' => false);
        return array('success'=>false,'error'=>$res['errorDescription'],'data'=>false);
    }
    public static function GetIndexGuangGaoCS($data){
        $url = self::HOST_URL . 'GetIndexGuangGaoCS';
        $res = self::http($url,$data,'POST');
        if(!$res)   return array('success'=>false,'error'=>"接口不存在或参数错误",'data'=>false);
        if(!$res['errorCode'])  return array('success' => true, 'error' => false, 'data' => $res['result']);
        return array('success'=>false,'error'=>$res['errorDescription'],'data'=>false);
    }
    public static function RemoveIndexCS($data){
        $url = self::HOST_URL . 'RemoveIndexCS';
        $res = self::http($url,$data,'POST');
        if(!$res)   return array('success'=>false,'error'=>"接口不存在或参数错误",'data'=>false);
        if(!$res['errorCode'])  return array('success' => true, 'error' => false, 'data' => false);
        return array('success'=>false,'error'=>$res['errorDescription'],'data'=>false);
    }

    public static function SaveIndexGuangGaoFooter($data){
        $url = self::HOST_URL . 'SaveIndexGuangGaoFooter';
        $res = self::http($url,$data,'POST');
        if(!$res)   return array('success'=>false,'error'=>"接口不存在或参数错误",'data'=>false);
        if(!$res['errorCode'])  return array('success' => true, 'error' => false, 'data' => false);
        return array('success'=>false,'error'=>$res['errorDescription'],'data'=>false);
    }
    public static function GetIndexGuangGaoFooter($data){
        $url = self::HOST_URL . 'GetIndexGuangGaoFooter';
        $res = self::http($url,$data,'POST');
        if(!$res)   return array('success'=>false,'error'=>"接口不存在或参数错误",'data'=>false);
        if(!$res['errorCode'])  return array('success' => true, 'error' => false, 'data' => $res['result']);
        return array('success'=>false,'error'=>$res['errorDescription'],'data'=>false);
    }
    public static function RemoveIndexGuangGaoFooter($data){
        $url = self::HOST_URL . 'RemoveIndexGuangGaoFooter';
        $res = self::http($url,$data,'POST');
        if(!$res)   return array('success'=>false,'error'=>"接口不存在或参数错误",'data'=>false);
        if(!$res['errorCode'])  return array('success' => true, 'error' => false, 'data' => false);
        return array('success'=>false,'error'=>$res['errorDescription'],'data'=>false);
    }

    public static function UpdateIndexWeekShow($data){
        $url = self::HOST_URL . 'UpdateIndexWeekShow';
        $res = self::http($url,$data,'POST');
        if(!$res)   return array('success'=>false,'error'=>"接口不存在或参数错误",'data'=>false);
        if(!$res['errorCode'])  return array('success' => true, 'error' => false, 'data' => false);
        return array('success'=>false,'error'=>$res['errorDescription'],'data'=>false);
    }

    public static function InsertIndexWeekShow($data){
        $url = self::HOST_URL . 'InsertIndexWeekShow';
        $res = self::http($url,$data,'POST');
        if(!$res)   return array('success'=>false,'error'=>"接口不存在或参数错误",'data'=>false);
        if(!$res['errorCode'])  return array('success' => true, 'error' => false, 'data' => false);
        return array('success'=>false,'error'=>$res['errorDescription'],'data'=>false);
    }
    public static function RemoveIndexWeekShow($data){
        $url = self::HOST_URL . 'RemoveIndexWeekShow';
        $res = self::http($url,$data,'GET');
        if(!$res)   return array('success'=>false,'error'=>"接口不存在或参数错误",'data'=>false);
        if(!$res['errorCode'])  return array('success' => true, 'error' => false, 'data' => false);
        return array('success'=>false,'error'=>$res['errorDescription'],'data'=>false);
    }
    public static function CreateIndexWeekShow($data){
        $url = self::HOST_URL . 'CreateIndexWeekShow';
        $res = self::http($url,$data,'POST');
        if(!$res)   return array('success'=>false,'error'=>"接口不存在或参数错误",'data'=>false);
        if(!$res['errorCode'])  return array('success' => true, 'error' => false, 'data' => false);
        return array('success'=>false,'error'=>$res['errorDescription'],'data'=>false);
    }

    public static function SaveVideoHotGame($data){
        $url = self::HOST_URL . 'SaveVideoHotGame';
        echo $url;
        $res = self::http($url,$data,'POST');
        if(!$res)   return array('success'=>false,'error'=>"接口不存在或参数错误",'data'=>false);
        if(!$res['errorCode'])  return array('success' => true, 'error' => false, 'data' => false);
        return array('success'=>false,'error'=>$res['errorDescription'],'data'=>false);
    }

    public static function GetVideoHotGame($data){
        $url = self::HOST_URL . 'GetVideoHotGame';
        $res = self::http($url,$data,'POST');
        if(!$res)   return array('success'=>false,'error'=>"接口不存在或参数错误",'data'=>false);
        if(!$res['errorCode'])  return array('success' => true, 'error' => false, 'data' => $res['result']);
        return array('success'=>false,'error'=>$res['errorDescription'],'data'=>false);
    }

    public static function RemoveVideoHotGame($data){
        $url = self::HOST_URL . 'RemoveVideoHotGame';
        $res = self::http($url,$data,'POST');
        if(!$res)   return array('success'=>false,'error'=>"接口不存在或参数错误",'data'=>false);
        if(!$res['errorCode'])  return array('success' => true, 'error' => false, 'data' => false);
        return array('success'=>false,'error'=>$res['errorDescription'],'data'=>false);
    }
}