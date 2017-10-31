<?php
use Illuminate\Support\Facades\Config;
return array(
    'module_name'   => 'v4a_giftbag',
    'module_type'   => 'android',
    'module_alias'  => '礼包v4a',
    'module_desc'   => '包括礼包管理等v4a',
    'Report' => array(
        //http://test.open.youxiduo.com/doc/interface-info/224
        'list'=>Config::get('app.mall_mml_api_url').'product/exportquery',
        'opercount'=>Config::get('app.mall_mml_api_url').'product/opercount',
    ),
    'Giftcard' => array(
        'list'=>Config::get('app.ios_virtual_card_url').'virtualcard/info_list',
        'delect'=>Config::get('app.ios_virtual_card_url').'virtualcard/delete',
        'callListUrl'=>'v4agiftbag/giftcard/list',
    )
);