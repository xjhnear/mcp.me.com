<?php

return array(

    'id' => 'captcha',
    'fontsizes' => array(14, 15, 16, 15, 14,16),
    'length' => 5,
    'width' => 120,
    'height' => 28,
    'space' => 20,
    'type' => 'alpha', // alpha or alnum or custom
    'custom_chars' => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
    'colors' => array('128,23,23', '128,23,22', '33,67,87', '67,70,83', '31,56,163', '48,109,22', '165,42,166', '18,95,98', '213,99,8'),
    'sensitive' => false // case sensitive (params: true, false)

);