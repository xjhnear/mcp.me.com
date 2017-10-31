admin@somethingyouxiduo

游戏多3.0项目说明

结构
yxd_minbbs
----apps //应用目录
--------admin //管理后台
--------api //前端接口
----config //全局配置目录
----doc //文档
----libraries //第三方类库
----services //核心服务
----vendor //基础框架
----web //Web入口
--------admin //后台管理Web入口
--------api //前端接口Web入口

runtime //程序运行时文件目录
----cache //缓存目录
----meta //框架运行时目录
----logs //运行时日志目录
----session //Session目录
----userdirs //用户上传文件目录
----views //运行是模板缓存目录

开发环境安装
第一步：
1、从SVN Checkout代码到本地，如:yxd_minbbs
2、在yxd_minbbs同级目录下新建目录runtime及相关子目录
3、在doc目录下有数据库结构脚本文件和初始化数据脚本文件，建数据库
4、修改apps/admin/config/dev和apps/api/config/dev下的配置文件:database.php配置相关数据库

第二步:
修改apache配置文件，新建如下虚拟主机
<VirtualHost *:80>
	ServerAlias api.youxiduo.dev
	DocumentRoot "D:/phpweb/yxd_minbbs/web/api"
	Alias /upload/ "D:/phpweb/runtime/upload/"
	<Directory "D:/phpweb/yxd_minbbs/web/api">
	    #Options Indexes FollowSymLinks Includes ExecCGI
		AllowOverride All
		Order allow,deny
        Allow from all
	</Directory>
</VirtualHost>

<VirtualHost *:80>
	ServerAlias mcp.youxiduo.dev
	DocumentRoot "D:/phpweb/yxd_minbbs/web/admin"
	Alias /upload/ "D:/phpweb/runtime/upload/"
	Alias /public/ "D:/phpweb/yxd_minbbs/web/bbs/public/"
	<Directory "D:/phpweb/yxd_minbbs/web/admin">
	    #Options Indexes FollowSymLinks Includes ExecCGI
		AllowOverride All
		Order allow,deny
        Allow from all
	</Directory>
</VirtualHost>
第三步：
修改操作系统的hosts文件
新建两个映射
127.0.0.1 mcp.youxiduo.dev
127.0.0.1 api.youxiduo.dev

