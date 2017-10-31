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
    public static function SaveIndexHeaderVideo($idx,$picUrl,$summary,$videoUrl,$redirecturl,$videoLive,$picMin)
    {
        $url = self::HOST_URL . 'SaveIndexHeaderVideo';
        $params = array(
            'idx'=>$idx,
            'picUrl'=>$picUrl,
            'summary'=>$summary,
            'videoUrl'=>$videoUrl,
            'videoLive'=>$videoLive,
            'picAtt'=>$redirecturl,
            'picMin'=>$picMin
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
}