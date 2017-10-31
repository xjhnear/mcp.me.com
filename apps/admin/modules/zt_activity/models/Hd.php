<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/3
 * Time: 14:07
 */

namespace modules\zt_activity\models;

class Hd extends BaseHttp
{
    public static function search($pageIndex=1,$pageSize=10)
    {
        $params = array('page'=>$pageIndex,'max'=>$pageSize);
        $url = self::HOST_URL . 'activitydao/jayce/listActivity';
        $result = self::http($url,$params);
        return $result;
    }

    /**
     * @param $pagetitle
     * @param $pagedate
     * @param $pagebg
     * @param $gameicon
     * @param $gamedesc
     * @param $iosdown
     * @param $anddown
     * @param $video
     * @param $activitydesc
     * @param $prizes
     * @param $partake
     * @param $pics
     * @param $videopic
     * @param $ofurl
     * @param $zqurl
     * @return bool|mixed
     */
    public static function add($pagetitle,$pagedate,$pagebg,$gameicon,$gamedesc,$iosdown,$anddown,$video,$activitydesc,$prizes,$partake,$pics,$videopic,$ofurl,$zqurl)
    {
        $params = array();
        $params['pagetitle'] = $pagetitle;
        $params['pagedate'] = $pagedate;
        $params['pagebg'] = $pagebg;
        $params['gameicon'] = $gameicon;
        $params['gamedesc'] = $gamedesc;
        $params['iosdown'] = $iosdown;
        $params['anddown'] = $anddown;
        $params['video'] = $video;
        $params['activitydesc'] = $activitydesc;
        $params['prizes'] = $prizes;
        $params['partake'] = $partake;
        $params['pics'] = $pics;
        $params['videopic'] = $videopic;
        $params['ofurl'] = $ofurl;
        $params['zqurl'] = $zqurl;

        $url = self::HOST_URL . 'activitydao/jayce/addActivity';
        return self::http($url,$params,'GET');
    }

    /**
     * @param $id
     * @param $pagetitle
     * @param $pagedate
     * @param $pagebg
     * @param $gameicon
     * @param $gamedesc
     * @param $iosdown
     * @param $anddown
     * @param $video
     * @param $activitydesc
     * @param $prizes
     * @param $partake
     * @param $pics
     * @param $videopic
     * @param $ofurl
     * @param $zqurl
     * @return bool|mixed
     */
    public static function update($id,$pagetitle,$pagedate,$pagebg,$gameicon,$gamedesc,$iosdown,$anddown,$video,$activitydesc,$prizes,$partake,$pics,$videopic,$ofurl,$zqurl)
    {
        $params = array();
        $params['id'] = $id;
        $params['pagetitle'] = $pagetitle;
        $params['pagedate'] = $pagedate;
        $params['pagebg'] = $pagebg;
        $params['gameicon'] = $gameicon;
        $params['gamedesc'] = $gamedesc;
        $params['iosdown'] = $iosdown;
        $params['anddown'] = $anddown;
        $params['video'] = $video;
        $params['activitydesc'] = $activitydesc;
        $params['prizes'] = $prizes;
        $params['partake'] = $partake;
        $params['pics'] = $pics;
        $params['videopic'] = $videopic;
        $params['ofurl'] = $ofurl;
        $params['zqurl'] = $zqurl;

        $url = self::HOST_URL . 'activitydao/jayce/updateActivity';
        return self::http($url,$params,'GET');
    }

    public static function delete($id)
    {
        $params = array();
        $params['id'] = $id;
        $url = self::HOST_URL . 'activitydao/jayce/removeActivity';
        return self::http($url,$params,'GET');
    }

    public static function info($id)
    {
        $params = array();
        $params['id'] = $id;
        $url = self::HOST_URL . 'activitydao/jayce/getActivity';
        $result = self::http($url,$params,'GET');
        return $result['activity'];
    }
}