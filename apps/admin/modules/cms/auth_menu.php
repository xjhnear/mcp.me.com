<?php
return array(
	'module_name' => 'cms',
	'module_alias' => '资讯',
	'default_url' => '',
	'child_menu' => array(
		array('name' => '新闻','url' => 'cms/news/search'),
		array('name' => '攻略','url' => 'cms/guide/search'),
		array('name' => '视频','url' => 'cms/video/search'),
        array('name' => '视频类型','url' => 'cms/vtype/search'),
		array('name' => '游戏视频','url' => 'cms/gamevideo/search'),
		array('name' => '评测','url' => 'cms/opinion/search'),	
		array('name' => '图鉴','url' => 'cms/other/search?type=tujian'),
		array('name' => '资料','url' => 'cms/other/search?type=info'),
		array('name' => '图片','url' => 'cms/other/search?type=picture'),
		array('name' => '年终总结评论','url' => 'cms/summary/search/0'),
	),
	'extra_node' => array(
		array('name' => '全部资讯模块','url' => 'cms/*')
	)
);