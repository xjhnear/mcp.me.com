<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/5/14
 * Time: 16:30
 */
namespace Youxiduo\Zhibo;


use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Youxiduo\Zhibo\Model\ZhiboGame;
use Youxiduo\Zhibo\Model\ZhiboGuest;
use Youxiduo\Zhibo\Model\ZhiboPlat;


class ZhiboService extends BaseService
{
    //获取直播列表
    public static function getGameList($page = 1 , $pagesize = 10 , $feilds = array(),$where = array()){
        $result = ZhiboGame::getList($page , $pagesize , $feilds,$where);
        foreach($result['result'] as &$v){
            $v['icon'] = Utility::getImageUrl($v['icon']);
        }
        return $result;
    }

    //获取主播/嘉宾列表
    public static function getGuestList($page = 1 , $pagesize = 10 , $feilds = array(),$where = array()){
        $result = ZhiboGuest::getList($page , $pagesize , $feilds,$where);
        foreach($result['result'] as &$v){
            $v['webpic'] = Utility::getImageUrl($v['webpic']);
            $v['h5pic'] = Utility::getImageUrl($v['h5pic']);
        }
        return $result;
    }

    //获取直播平台列表
    public static function getPlatList($page = 1 , $pagesize = 10 , $feilds = array(),$where = array()){
        $result = ZhiboPlat::getList($page , $pagesize , $feilds,$where);
        foreach($result['result'] as &$v){
            $v['icon'] = Utility::getImageUrl($v['icon']);
            $v['icon_hover'] = Utility::getImageUrl($v['icon_hover']);
            $v['h5_icon'] = Utility::getImageUrl($v['h5_icon']);
        }
        return $result;
    }

}