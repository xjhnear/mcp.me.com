<?php
use Youxiduo\V4\Game\GameService;
use Youxiduo\V4\User\UserService;

return array(
    UserService::ERROR_CREATE_USER_ERROR => '注册失败',
    UserService::ERROR_EMAIL_FORMAT_INVALID => '邮箱格式错误',
    UserService::ERROR_LOGIN_ERROR => '登录失败',
    UserService::ERROR_MOBILE_EXISTS => '手机号已经被占用',
    UserService::ERROR_MOBILE_FORMAT_INVALID => '手机格式错误',
    UserService::ERROR_MOBILE_NOT_VERIFY => '手机未验证',
    UserService::ERROR_MODIFY_PASSWORD_ERROR => '密码错误',
    UserService::ERROR_MODIFY_USER_ERROR => '修改用户资料失败',
    UserService::ERROR_NICKNAME_EXISTS => '昵称已被占用',
    UserService::ERROR_PASSWORD_EMPTY => '密码不能为空',
    UserService::ERROR_SMS_VERIFYCODE_ERROR => '短信验证码错误',
    UserService::ERROR_SMS_VERIFYCODE_FAILURE => '短信验证码无效',
    UserService::ERROR_USER_NOT_EXISTS => '用户信息不存在',
    UserService::ERROR_MONTH_CHECK => '每台设备每个月只能注册2次',
    
    
    GameService::ERROR_GAME_NOT_EXISTS => '游戏不存在',
    GameService::ERROR_PLATFORM_NOT_EXISTS => '平台参数错误',
    GameService::ERROR_SPECIAL_TOPIC_NOT_EXISTS => '特色专题不存在',
    
);