# ledis 的docker镜像

## 使用
+ 暴露了 6380 和 11181 端口 分别用于addr 和 http_addr
+ 默认的conf路径为/etc/conf/ledis.conf需要替换时覆盖即可
+ 默认的log目录为 /tmp/log/ledis.log,数据存放位置为 /tmp/ledis_server