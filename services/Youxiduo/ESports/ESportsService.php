<?php
namespace Youxiduo\ESports;

use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Config;
use modules\yxvl_eSports\models\BaseHttp;

class ESportsService extends BaseService{

    const API_URL_CONF = "app.ESports_api_url";
    const API_URL_CONF2 = "app.ESports_api_url2";

    public static $arr = array(
        'CreateArticleCatalog' => array("youxidj-backstage/CreateArticleCatalog",'GET'),//创建分类--文章
        'UpdateArticleCatalog' => array("youxidj-backstage/UpdateArticleCatalog",'GET'),//更新分类--文章
        'GetArticleCatalogs' => array("youxidj-backstage/GetArticleCatalogs",'GET'),//分类列表--文章
        'CreateArticle' => array("youxidj-backstage/CreateArticle",'POST'),//创建--文章
        'GetArticleList' => array("youxidj-backstage/GetArticleList",'GET'),//列表--文章
        'GetArticleDetail' => array("youxidj-backstage/GetArticleDetail",'GET'),//详情--文章
        'UpdateArticle' => array("youxidj-backstage/UpdateArticle",'POST'),//更新--文章
        'RemoveArticle' => array("youxidj-backstage/RemoveArticle",'GET'),//删除--文章

        'CreateColumnCatalog' => array("youxidj-backstage/CreateColumnCatalog",'GET'),//创建分类--专栏
        'UpdateColumnCatalog' => array("youxidj-backstage/UpdateColumnCatalog",'GET'),//更新分类--专栏
        'GetColumnCatalogs' => array("youxidj-backstage/GetColumnCatalogs",'GET'),//分类列表--专栏
        'CreateColumn' => array("youxidj-backstage/CreateColumn",'POST'),//创建--专栏
        'GetColumnList' => array("youxidj-backstage/GetColumnList",'GET'),//列表--专栏
        'GetColumnDetail' => array("youxidj-backstage/GetColumnDetail",'GET'),//详情--专栏
        'UpdateColumn' => array("youxidj-backstage/UpdateColumn",'POST'),//更新--专栏
        'RemoveColumn' => array("youxidj-backstage/RemoveColumn",'GET'),//删除--专栏

        'CreateLiveCatalog' => array("youxidj-backstage/CreateLiveCatalog",'GET'),//创建分类--直播
        'UpdateLiveCatalog' => array("youxidj-backstage/UpdateLiveCatalog",'GET'),//更新分类--
        'GetLiveCatalogs' => array("youxidj-backstage/GetLiveCatalogs",'GET'),//分类列表--
        'CreateLive' => array("youxidj-backstage/CreateLive",'GET'),//创建--
        'GetLiveList' => array("youxidj-backstage/GetLiveList",'GET'),//列表--
        'GetLiveDetail' => array("youxidj-backstage/GetLiveDetail",'GET'),//详情--
        'UpdateLive' => array("youxidj-backstage/UpdateLive",'GET'),//更新--
        'RemoveLive' => array("youxidj-backstage/RemoveLive",'GET'),//删除--

        'CreateVideoCatalog' => array("youxidj-backstage/CreateVideoCatalog",'GET'),//创建分类--直播
        'UpdateVideoCatalog' => array("youxidj-backstage/UpdateVideoCatalog",'GET'),//更新分类--
        'GetVideoCatalogs' => array("youxidj-backstage/GetVideoCatalogs",'GET'),//分类列表--
        'CreateVideo' => array("youxidj-backstage/CreateVideo",'GET'),//创建--
        'GetVideoList' => array("youxidj-backstage/GetVideoList",'GET'),//列表--
        'GetVideoDetail' => array("youxidj-backstage/GetVideoDetail",'GET'),//详情--
        'UpdateVideo' => array("youxidj-backstage/UpdateVideo",'GET'),//更新--
        'RemoveVideo' => array("youxidj-backstage/RemoveVideo",'GET'),//删除--

        'CreateSaiShi' => array("youxidj-backstage/CreateSaiShi",'GET'),//创建--
        'GetSaiShiList' => array("youxidj-backstage/GetSaiShiList",'GET'),//列表--
        'GetSaiShiDetail' => array("youxidj-backstage/GetSaiShiDetail",'GET'),//详情--
        'UpdateSaiShi' => array("youxidj-backstage/UpdateSaiShi",'GET'),//更新--
        'RemoveSaiShi' => array("youxidj-backstage/RemoveSaiShi",'GET'),//删除--

        'SaveIndexGameVideo' => array("youxidj-backstage/SaveIndexGameVideo",'GET'),//创建--首页中间游戏直播视频
        'GetIndexGameVideo' => array("youxidj-backstage/GetIndexGameVideo",'GET'),//获取--首页中间游戏直播视频

        'SaveIndexGameZQ' => array("youxidj-backstage/SaveIndexGameZQ",'GET'),//创建--头部游戏专区
        'GetIndexGameZQ' => array("youxidj-backstage/GetIndexGameZQ",'GET'),//获取--头部游戏专区

        'SaveIndexHotDJ' => array("youxidj-backstage/SaveIndexHotDJ",'GET'),//创建--首页头部热门电竞
        'GetIndexHotDJ' => array("youxidj-backstage/GetIndexHotDJ",'GET'),//获取--首页头部热门电竞

        'SaveIndexLeftHuanDeng' => array("youxidj-backstage/SaveIndexLeftHuanDeng",'GET'),//创建--首页头部左侧幻灯
        'GetIndexLeftHuanDeng' => array("youxidj-backstage/GetIndexLeftHuanDeng",'GET'),//获取--首页头部左侧幻灯

        'SaveIndexRightSaiShi' => array("youxidj-backstage/SaveIndexRightSaiShi",'GET'),//创建--首页头部右侧热门赛事
        'GetIndexRightSaiShi' => array("youxidj-backstage/GetIndexRightSaiShi",'GET'),//获取--首页头部右侧热门赛事

        'SaveIndexSaiShiTime' => array("youxidj-backstage/SaveIndexSaiShiTime",'GET'),//创建--首页赛事中心的赛事时间
        'GetIndexSaiShiTime' => array("youxidj-backstage/GetIndexSaiShiTime",'GET'),//获取--首页赛事中心的赛事时间

        'SaveIndexSaiShiZhanDui' => array("youxidj-backstage/SaveIndexSaiShiZhanDui",'GET'),//创建--首页赛事中心的赛事战队
        'GetIndexSaiShiZhanDui' => array("youxidj-backstage/GetIndexSaiShiZhanDui",'GET'),//获取--首页赛事中心的赛事战队

        'SaveIndexGuangGaoFooter' => array("youxidj-backstage/SaveIndexGuangGaoFooter",'GET'),//创建--底部广告位跳转路径及图片
        'GetIndexGuangGaoFooter' => array("youxidj-backstage/GetIndexGuangGaoFooter",'GET'),//获取--底部广告位跳转路径及图片

        'SaveIndexGuangGaoHeader' => array("youxidj-backstage/SaveIndexGuangGaoHeader",'GET'),//创建--头部广告位跳转路径及图片
        'GetIndexGuangGaoHeader' => array("youxidj-backstage/GetIndexGuangGaoHeader",'GET'),//获取--头部广告位跳转路径及图片

        'CreateWebPageDesc' => array("youxidj-backstage/CreateWebPageDesc",'GET'),//创建--header信息
        'GetWebPageDesc' => array("youxidj-backstage/GetWebPageDesc",'GET'),//获取--header信息
        'UpdateWebPageDesc' => array("youxidj-backstage/UpdateWebPageDesc",'GET'),//更新--header信息

        'CreateLiveInfo' => array("yxvl-backstage/CreateLiveInfo",'POST',"new"),//创建--直播
        'GetLiveInfo' => array("yxvl-backstage/GetLiveInfo",'GET',"new"),//获取--直播
        'UpdateLiveInfo' => array("yxvl-backstage/UpdateLiveInfo",'POST',"new"),//更新--直播
        'RemoveLiveInfo' => array("yxvl-backstage/RemoveLiveInfo",'GET',"new"),//删除--直播
        'GetLiveInfoList' => array("yxvl-backstage/GetLiveInfoList",'GET',"new"),//列表--直播

        'CreateLiveGraphical' => array("yxvl-backstage/CreateLiveGraphical",'POST',"new"),//创建--直播
        'GetLiveGraphical' => array("yxvl-backstage/GetLiveGraphical",'GET',"new"),//获取--直播
        'UpdateLiveGraphical' => array("yxvl-backstage/UpdateLiveGraphical",'POST',"new"),//更新--直播
        'RemoveLiveGraphical' => array("yxvl-backstage/RemoveLiveGraphical",'GET',"new"),//删除--直播
        'GetLiveGraphicalList' => array("yxvl-backstage/GetLiveGraphicalList",'GET',"new"),//列表--直播

        'CreateAppAccount' => array("yxvl-backstage/CreateAppAccount",'POST',"new"),//创建--用户
        'GetAppAccount' => array("yxvl-backstage/GetAppAccount",'GET',"new"),//获取--用户
        'UpdateAppAccount' => array("yxvl-backstage/UpdateAppAccount",'POST',"new"),//更新--用户
        'RemoveAppAccount' => array("yxvl-backstage/RemoveAppAccount",'GET',"new"),//删除--用户
        'GetAppAccountList' => array("yxvl-backstage/GetAppAccountList",'GET',"new"),//列表--用户


    );

    //完成
    public static function excute($data=array(),$method="",$isList=true)
    {
        if(isset(self::$arr[$method][2])&&self::$arr[$method][2]=="new"){
            $res = BaseHttp::http(Config::get(self::API_URL_CONF).self::$arr[$method][0],$data,self::$arr[$method][1]);
        }else{
            $res = BaseHttp::http(Config::get(self::API_URL_CONF2).self::$arr[$method][0],$data,self::$arr[$method][1]);
        }

//        echo Config::get(self::API_URL_CONF).self::$arr[$method][0];
//        print_r($data);
//        print_r($res);die;
        if(!$res){
            return array('success'=>false,'error'=>"接口不存在或参数错误",'data'=>false);
        }
        if(!$res['errorCode']) {
            if($isList){
                if(isset($res['result'])&&$res['result'])
                    return array('success' => true, 'error' => false, 'data' => $res['result']);
            }else{
                return array('success' => true, 'error' => false, 'data' => false);
            }
        }
        return array('success'=>false,'error'=>$res['errorDescription'],'data'=>false);
    }

    public static function excute2($data=array(),$method="",$isList=true)
    {
        if(isset(self::$arr[$method][2])&&self::$arr[$method][2]=="new"){
            $res = BaseHttp::http(Config::get(self::API_URL_CONF).self::$arr[$method][0],$data,self::$arr[$method][1]);
        }else{
            $res = BaseHttp::http(Config::get(self::API_URL_CONF2).self::$arr[$method][0],$data,self::$arr[$method][1]);
        }

//        echo Config::get(self::API_URL_CONF).self::$arr[$method][0];
//        print_r($data);
//        print_r($res);die;
        if(!$res){
            return array('success'=>false,'error'=>"接口不存在或参数错误",'data'=>false);
        }
        if(!$res['errorCode']) {
            if($isList){
                if(isset($res['result'])&&$res['result'])
                return array('success' => true, 'error' => false, 'data' => $res['result']);
            }else{
                return array('success' => true, 'error' => false, 'data' => false);
            }
        }
        return array('success'=>false,'error'=>$res['errorDescription'],'data'=>false);
    }

  }