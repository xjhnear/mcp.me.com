<?php
$v3_tables = require 'dbtable_v3.php';
$v4_tables = array(

    'youxiduo_system_model_admin'=>array('db'=>'cms','table'=>'admin'),
    'youxiduo_system_model_module'=>array('db'=>'system','table'=>'module'),
    'youxiduo_system_model_authgroup'=>array('db'=>'system','table'=>'auth_group'),
    'youxiduo_system_model_phonebatch'=>array('db'=>'cms','table'=>'phone_batch')


);
return array_merge($v4_tables,$v3_tables);