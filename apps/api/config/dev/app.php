<?php

return array(
    'debug' => true,
    'close_cache' => true,
    'api'=>'http://api.youxiduo.dev',
    'oauth2'=>array(
        'driver'=>'pdo',
        'pdo'=>array(
            'dsn'=>'mysql:dbname=yxd_minbbs;host=localhost',
            'username'=>'cwebgame',
            'password'=>'Mlt9khwywk'
        ),
        'redis'=>array()
        
    ),
);
