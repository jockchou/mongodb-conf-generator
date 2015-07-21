# MongoDB3.0集群配置文件自动生成器 #


----------


## 文件说明： ##
```
	-- cfg.conf.template
	-- mongod.conf.template
	-- mongos.conf.template

```


> cfg.conf.template   ： 集群配制服务器配制文件模板  
> mongod.conf.template： mongod进程配制文件模板  
> mongos.conf.template： mongos路由进程配制文件模板  
> generator.php       ： 自动生成配制文件的PHP脚本  


## 脚本使用方法： ##

在你的Linux服务器上创建一个新目录，我这里创建的目录是/data/mongo

```
mkdir -p /data/mongo
```
复制本项目录下上述四个文件到/data/mongo目录中，执行php generator.php。执行前先确保机器上已经安装PHP。


## 配制说明： ##
generator.php文件的上面定义了一些常量，你可以修改这些常量的值。

```
//帮定IP
define("BIND_IP", "127.0.0.1,192.168.200.2");

//配置服务器IP，IP个数与配置服务器个数保持相同
define("CFG_IP", "192.168.200.2,192.168.200.2,192.168.200.2");

//mongodb bin目录
define("BINPATH", "/usr/local/mongodb/bin");

//超始端口号
define("START_PORT", 40000);

//配置服务器个数
define("CFG_SVR_NUM", 3);

//分片个数
define("RS_NUM", 3);

//复制集节点数
define("RS_NODE_NUM", 3);
```

## 模板修改: ##
三个模板文件定义了MongoDB各进程的常见配置，你可以修改这些配置项。##是将被替换的动态部分。


##脚本输出：##

运行脚本后，你将看到类似以下输出：

```
C:\data\GitRoot\mongodb-conf-generator>php generator.php

cfg->node0->4000
cfg->node1->4001
cfg->node2->4002

rs0->node0->4003
rs0->node1->4004
rs0->node2->4005

rs1->node0->4006
rs1->node1->4007
rs1->node2->4008

rs2->node0->4009
rs2->node1->4010
rs2->node2->4011

mongos----->4012
```

- cfg： 表示配置服务器  
- node： 表示节点  
- rs： 示是复制集  
- 4000： 最右边的数字是端口号  