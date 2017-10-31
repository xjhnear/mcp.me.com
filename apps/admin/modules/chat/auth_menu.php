<?php
return array(
	'module_name' => 'chat',
	'module_alias' => '聊天室',
	'default_url' => 'chat/chatroom/chatroom-list',
	'child_menu' => array(
		array('name' => '聊天室列表','url' => 'chat/chatroom/chatroom-list'),
        array('name' => '我的消息','url' => 'chat/chatroom/my-message'),
        array('name' => '小秘书活动','url' => 'chat/chatactivity/list'),
	),
	'extra_node' => array(
		array('name' => '全部聊天模块','url' => 'chat/*')
	)
);