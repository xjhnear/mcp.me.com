<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/5/4
 * Time: 10:09
 */
namespace Youxiduo\Activity\Blcx;

use Youxiduo\Activity\Model\ActivityBlcxPreson;
use Youxiduo\Helper\Utility;


class BlcxService{
    //获取月儿海选列表
    public static function getAuditList($audit=''){
        $result = ActivityBlcxPreson::getLists($audit);
        foreach($result as &$v){
            $v['pics'] = Utility::getImageUrl($v['pics']);
            $v['singlepic'] = Utility::getImageUrl($v['singlepic']);
        }
        return $result;
    }
}
