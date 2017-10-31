<?php
/**
 * Created by PhpStorm.
 * User: fujiajun
 * Date: 15/12/23
 * Time: 下午4:53
 */

namespace Youxiduo\V4\Advs;
use Youxiduo\MyService\QueryService;
use Youxiduo\Adv\AdvService;

class AdvV4Service
{
        protected static $database='yxd_advert.yxd_advert_v4appadv';

        public function __construct()
        {

        }

        public static function getByList($inputinfo=array())
        {
            QueryService::$databas_table=self::$database;
            if (isset($inputinfo['gid'])) {
                $inputinfo['game_id'] = $inputinfo['gid'];
                unset($inputinfo['gid']);
            }
            $inputinfo['where']=$inputinfo;
            $inputinfo['sqlWhere']=array('startTime'=>' startTime < ? ','endTime'=>' endTime > ? ');
            $inputinfo['orderby']=' ORDER BY  sort ASC ,endTime ASC';
            //$inputinfo['adv_logo']='首页弹窗';
            $item = QueryService::getListByApi($inputinfo);
            $result = array();
            foreach ($item as $k=>&$v) {
                $third=AdvService::FindAdvthird(' advid="'.$v['id'].'"');
                $v['thirdVendorsList'] = array();
                foreach ($third as $item) {
                    $v['thirdVendorsList'][] = $item;
                }
                $result[$v['sort']] = $v;
            }
            return array_merge($result);
        }

        public static function statistics($inputinfo=array())
        {
            QueryService::$databas_table='yxd_advert.yxd_advert_statisticss';
            return QueryService::addData($inputinfo);
        }



}