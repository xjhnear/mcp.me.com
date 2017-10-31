<?php
return array(
    'module_name'  => 'sproject',
    'module_alias' => '小秘书认证信息',
    'default_url'=>'sproject/attestation/edit',
    'child_menu' => array(
        array('name'=>'认证信息','url'=>'sproject/attestation/edit'),
    ),
    'extra_node'=>array(  
        array('name'=>'全部小秘书模块','url'=>'sproject/*')      
    )
);