<?php
require 'http.php';
/*
//发问答帖
//$url = 'http://api.youxiduo.dev/topic/post-ask';
$url = 'http://open.youxiduo.com/topic/post-topic';
$params = array('uid'=>40,'gid'=>'100','articleType'=>'3','title'=>'这个是提问呀','award'=>10,
	'contentBlocks'=>json_encode(array(array('content'=>'内容1','photoName'=>'thread1'),array('content'=>'内容1','photoName'=>'thread2')))    
);
$file = array('thread1'=>'D:\phpweb\demo\abc.jpg','thread2'=>'D:\phpweb\demo\abcd.png');
//$file = false;
$result = CwanHttp::request($url,$params,'POST',$file);
print_r($result);
exit;
*/
//评论
//$url = 'http://api.youxiduo.dev/comment/post-comment';
$url = 'http://open.youxiduo.com/comment/post-comment';
$params = array('uid'=>1,'aid'=>'121','typeid'=>'0','replyTopic'=>0,'replyuid'=>0,
	'contentBlocks'=>json_encode(array(array('content'=>'内容1','photoName'=>'thread1'),array('content'=>'内容1','photoName'=>'thread2')))
    
);
$file = array('thread1'=>'D:\phpweb\demo\abc.jpg','thread2'=>'D:\phpweb\demo\abcd.png');
//$file = false;
$result = CwanHttp::request($url,$params,'POST',$file);
print_r($result);
