<?php
namespace modules\gamelive\models;

class Game extends BaseHttp
{
    /**
     * @param $name
     * @param $gicon
     * @param $titlePic
     * @param $summary
     * @param $top
     * @param $defVideo
     * @param $publishTime
     * @return null
     */
    public static function CreateGame($name,$gicon,$titlePic,$summary,$top,$defVideo,$publishTime)
    {
        $url = self::HOST_URL . 'CreateGame';
        $params = array(
            'name'=>$name,
            'gicon'=>$gicon,
            'titlePic'=>$titlePic,
            'summary'=>$summary,
            'top'=>$top,
            'defVideo'=>$defVideo,
            'publishTime'=>$publishTime
        );
        $result = self::http($url,$params);
        if($result['errorCode']==0){
            return $result['result'];
        }
        exit($result['errorDescription']);
        return null;
    }

    /**
     * @param $id
     * @param $name
     * @param $gicon
     * @param $titlePic
     * @param $summary
     * @param $top
     * @param $defVideo
     * @param $publishTime
     * @return bool|null
     */
    public static function UpdateGame($id,$name,$gicon,$titlePic,$summary,$top,$defVideo,$publishTime)
    {
        $url = self::HOST_URL . 'UpdateGame';
        $params = array(
            'id'=>$id,
            'name'=>$name,
            'gicon'=>$gicon,
            'titlePic'=>$titlePic,
            'summary'=>$summary,
            'top'=>$top,
            'defVideo'=>$defVideo,
            'publishTime'=>$publishTime
        );
        $result = self::http($url,$params);
        if($result['errorCode']==0){
            return true;
        }
        exit($result['errorDescription']);
        return null;
    }

    /**
     * @param $id
     * @return null
     */
    public static function GetGameDetail($id)
    {
        $url = self::HOST_URL . 'GetGameDetail';
        $params = array(
            'id'=>$id
        );
        $result = self::http($url,$params);
        if($result['errorCode']==0&&isset($result['result'])){
            return $result['result'];
        }
        return null;
    }
    /**
     * @param $ids
     * @return null
     */
    public static function GetGameListByID($ids="")
    {
        $list = array();
        $idArr = explode(',',$ids);
        if($idArr){
            foreach($idArr as $v){
               $res = self::GetGameDetail($v);
                if($res){
                    $list[] = $res;
                }
            }
            return $list;
        }
        return null;
    }

    /**
     * @param $ids
     * @return null
     */
    public static function GetGameNames($ids="")
    {
        $names = "";
        $list = self::GetGameListByID($ids);
        if($list){
            foreach($list as $v){
                $names.=  $v['name'].",";
            }
            return substr($names,0,-1);
        }
        return null;
    }

    /**
     * @param $page
     * @param $size
     * @return array
     */
    public static function GetGameList($page,$size)
    {
        $url = self::HOST_URL . 'GetGameList';
        $params = array(
            'page'=>$page,
            'size'=>$size
        );

        $result = self::http($url,$params);
        if($result['errorCode']==0){
            return $result['result'];
        }
        return array('totalPage'=>0,'size'=>0,'page'=>0,'list'=>array());
    }

    /**
     * @param $id
     * @return bool|null
     */
    public static function RemoveGame($id)
    {
        $url = self::HOST_URL . 'RemoveGame';
        $params['gameId'] = $id;
        $result = self::http($url,$params);
        if($result['errorCode']==0){
            return true;
        }
        return null;
    }
}