# 十二赞小程序全套源代码

十二赞团队投放了数百万人民币，开发了本套小程序系统 。后端代码主要是php构成，有少量的java\python\golang.前端全部是微信小程序原生，自定义装修部分也是全原生实现。

1. 十二赞小程序 全部版本。功能齐全，架构可扩展，支持千万级访问毫无压力。
2. 接下来，我会把十二赞旗下的全部小程序的源代码整理、开放出来。陆陆续续整理中。
3. 有时候因为要删掉一些敏感信息，保障现在十二赞的客户的信息安全，所以进展会慢一点，请大家耐心等待。也有可能删的内容影响到了现有小程序的运行，需要自己修改一下，请多多理解。
4. 目前十二赞已经实现了比较先进的一些东西，比如代码自动集成、docker自动发布、服务发现、服务器扩容， 这里的开放源码已经包含了后端的一些工具库、运维平台等等。
4. 大家有问题可以加我微信或QQ:55547082 ,有空的话我会回复一些问题，但是可能杂事繁多，无法一一回复，请见谅。![https://upshare.withpush.cn/201912/50635100.jpg](https://upshare.withpush.cn/201912/6464800.jpg?x-oss-process=style/w600)
5. 我开放了一个知识星球来回复这些问题，这样已经有人提过的问题，大家就直接能看到了，欢迎大家加入。![https://upshare.withpush.cn/201912/50635100.jpg](https://upshare.withpush.cn/201912/50635100.jpg?x-oss-process=style/w600)





# 小程序功能列表

### 基础模块

- 商品基础信息管理
- 分类管理
- 商品分组管理
- 自定义装修管理
- 运费模板管理
- 电子面单支持
- 小程序版本管理等等
- 子帐号管理

### 营销相关

- 积分
- 返利
- 优惠券
- 限时折扣
- 新人红包
- 裂变红包
- 0元购
- 拼团
- 抽奖
- 助力抽奖
- 扫码买单
- 运费优惠（包邮策略）
- 储值活动
- 评价活动
- 供应商管理

# 使用中的小程序（真实客户）预览

目前各个品类有近千客户。包括绿城集团、爱尔眼科、部分天猫店、餐饮、商场、实业、学校及医院食堂、花店、上市公司等。



###  部分界面截图

1. 助力抽奖

![https://upshare.withpush.cn/201912/47090800.8654.png?x-oss-process=style/w600](https://upshare.withpush.cn/201912/47090800.8654.png?x-oss-process=style/w600)
![https://upshare.withpush.cn/201912/26057000.8998.png?x-oss-process=style/w600](https://upshare.withpush.cn/201912/26057000.8998.png?x-oss-process=style/w600)
![https://cdn.withpush.cn/opensource/IMG_4821.PNG?x-oss-process=style/w600](https://cdn.withpush.cn/opensource/IMG_4821.PNG?x-oss-process=style/w600)
![https://cdn.withpush.cn/opensource/IMG_4822.PNG?x-oss-process=style/w600](https://cdn.withpush.cn/opensource/IMG_4822.PNG?x-oss-process=style/w600)


2. 生成分享图
![https://cdn.withpush.cn/opensource/IMG_4818.PNG?x-oss-process=style/w600](https://cdn.withpush.cn/opensource/IMG_4818.PNG?x-oss-process=style/w600)

3.扫码买单
![https://cdn.withpush.cn/opensource/IMG_4820.PNG?x-oss-process=style/w600](https://cdn.withpush.cn/opensource/IMG_4820.PNG?x-oss-process=style/w600)

4. 拼团
![https://cdn.withpush.cn/opensource/IMG_4817.PNG?x-oss-process=style/w600](https://cdn.withpush.cn/opensource/IMG_4817.PNG?x-oss-process=style/w600)

5.限时折扣
![https://cdn.withpush.cn/opensource/IMG_4815.PNG?x-oss-process=style/w600](https://cdn.withpush.cn/opensource/IMG_4815.PNG?x-oss-process=style/w600)

6.积分
![https://cdn.withpush.cn/opensource/IMG_4814.PNG?x-oss-process=style/w600](https://cdn.withpush.cn/opensource/IMG_4814.PNG?x-oss-process=style/w600)

7. 裂变红包
![https://cdn.withpush.cn/opensource/IMG_4813.PNG?x-oss-process=style/w600](https://cdn.withpush.cn/opensource/IMG_4813.PNG?x-oss-process=style/w600)

8. 返利
- ![https://upshare.withpush.cn/201912/13969000.9229.png](https://upshare.withpush.cn/201912/13969000.9229.png?x-oss-process=style/w600)
- ![https://upshare.withpush.cn/201912/41076800.4477.png](https://upshare.withpush.cn/201912/41076800.4477.png?x-oss-process=style/w600)

# 部署说明

1. 十二赞的后端代码，如果是基于php下laravel的，数据库的建立和字段修改是通过migration来实现的，即运行php artisan migrate 即可自动完成建表、初始化数据等操作。
2. Java版的程序基本是基于spring-boot的，打包成jar包，直接运行，会自动建表。

# 运维相关的基础组件介绍
十二赞依赖了一些像缓存、队列等基础组件。以下是相关的基础组件的源代码或是Docker配置。

### Ledis
在部分场景下，十二赞用ledis替代了Redis;源代码在ledis目录下。（请注意这是十二赞修改过的,不是原生的ledis)

### ossup
为了方便java/nodejs/php等多种语言、及我们自己在办公环境常用的一个客户端的文件分享工具使用，我们编译了一个命令行版的oss上传工具，可以通过命令行调用将本地文件上传到oss上去。参见ossup目录。

### wordfilter
关键词过滤工具，因为最早的一个小程序有用户上传了一些敏感信息，导致我们第一个爆红的小程序直接就地死亡，我们特地自行开发了一套过滤接口来对用户输入作过滤；该过滤支持在敏感词有一定的掺杂，比如，若度假村是敏感词，输入中有‘你度了假的村’，也能被查找到。参见wordfilter目录。

### 盲水印支持(blindwatermark)
为向特定的一些大客户提供水印保护，我们提供了一个简单的盲水印实现，特点就是加了水印的图片普通用户肉眼看起来还是没有任何变化，但是经过还原，能看到很明显的水印。
即使是盗图分子截屏、裁切都依然会保留水印。参见blindwater目录 ；

### 内网穿透(pt)
为了微信调试方便，开发了内网穿透模块,参见pt目录。

### consulmanager 
一个小的consul的修复工具

### reporter
是服务发现的一个组件，会内置在所有Docker中;当Docker启动的时候即向中控汇报自己的IP和端口。参见 reporter目录 


# 小程序应用层源代码

### 美业小程序 （shop-beauty-client)
是美业小程序的完整客户端源代码；无任何删减，仅隐去了关键的secret和密码类信息。

### 美业小程序后台 (shop-beauty-backend)



