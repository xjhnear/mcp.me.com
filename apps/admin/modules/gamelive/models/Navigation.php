<?php
namespace modules\gamelive\models;

class Navigation extends BaseHttp
{
    /**
     * @param $page
     * @param $size
     * @param string $tag
     * @param string $albums
     * @param string $peoples
     * @return array
     */
    public static function GetColumnList($page,$size,$tag='',$albums='',$peoples='')
    {
        $url = self::HOST_URL . 'GetColumnList';
        $params = array(
            'page'=>$page,
            'size'=>$size
        );
        $result = self::http($url,$params);
        if($result['errorCode']==0){
            return $result['result'];
        }
        return array('totalPage'=>0,'page'=>0,'size'=>0,'list'=>array());
    }

    /**
     * @return array
     */
    public static function GetColumnOptions()
    {
        $result = self::GetColumnList(1,100);
        if($result['list']){
            $out = array(''=>'选择栏目');
            foreach($result['list'] as $row){
                $out[$row['id']] = $row['name'];
            }
            return $out;
        }
        return array();
    }

    /**
     * @param $name
     * @param $picUrl
     * @param $summary
     * @param $content
     * @param $publishTime
     * @param string $tag
     * @param $albums
     * @param $peoples
     * @param $thumbnail
     * @param $H5Code
     * @param $PCCode
     * @param $shareTitle
     * @param $sharePicUrl
     * @param $shareSummary
     * @return bool
     */
    public static function CreateColumn($name,$picUrl,$summary,$content,$publishTime,$tag='',$albums,$peoples,$thumbnail,$H5Code,$PCCode,$shareTitle,$sharePicUrl,$shareSummary)
    {
        $url = self::HOST_URL . 'CreateColumn';
        $params = array(
            'name'=>$name,
            'picUrl'=>$picUrl,
            'summary'=>$summary,
            'content'=>$content,
            'publishTime'=>$publishTime,
            'tag'=>$tag,
            'albums'=>$albums,
            'peoples'=>$peoples,
            'thumbnail'=>$thumbnail,
            'H5Code'=>$H5Code,
            'PCCode'=>$PCCode,
            'shareTitle'=>$shareTitle,
            'sharePicUrl'=>$sharePicUrl,
            'shareSummary'=>$shareSummary
        );
        $result = self::http($url,$params);
        if($result['errorCode']==0){
            return $result['result'];
        }
        return false;
    }

    /**
     * @param $id
     * @param $name
     * @param $picUrl
     * @param $summary
     * @param $content
     * @param $publishTime
     * @param string $tag
     * @param $albums
     * @param $peoples
     * @param $thumbnail
     * @param $H5Code
     * @param $PCCode
     * @param $shareTitle
     * @param $sharePicUrl
     * @param $shareSummary
     * @return bool
     */
    public static function UpdateColumn($id,$name,$picUrl,$summary,$content,$publishTime,$tag='',$albums,$peoples,$thumbnail,$H5Code,$PCCode,$shareTitle,$sharePicUrl,$shareSummary)
    {
        $url = self::HOST_URL . 'UpdateColumn';
        $params = array(
            'id'=>$id,
            'name'=>$name,
            'picUrl'=>$picUrl,
            'summary'=>$summary,
            'content'=>$content,
            'publishTime'=>$publishTime,
            'tag'=>$tag,
            'albums'=>$albums,
            'peoples'=>$peoples,
            'thumbnail'=>$thumbnail,
            'H5Code'=>$H5Code,
            'PCCode'=>$PCCode,
            'shareTitle'=>$shareTitle,
            'sharePicUrl'=>$sharePicUrl,
            'shareSummary'=>$shareSummary
        );
        $result = self::http($url,$params);
        if($result['errorCode']==0){
            return true;
        }
        exit($result['errorDescription']);
        return false;
    }

    /**
     * @param $id
     * @return bool
     */
    public static function RemoveColumn($id)
    {
        $url = self::HOST_URL . 'RemoveColumn';
        $params = array('columnid'=>$id);
        $result = self::http($url,$params);
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }

    /**
     * @param $id
     * @return bool
     */
    public static function GetColumnDetail($id)
    {
        $url = self::HOST_URL . 'GetColumnDetail';
        $params = array(
            'id'=>$id
        );
        $result = self::http($url,$params);
        if($result['errorCode']==0){
            return $result['result'];
        }
        return false;
    }


}