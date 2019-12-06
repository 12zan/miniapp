pt的目标在于建立一个这样的工具:
一个服务端(proxy)，一个客户端(agent)，客户端部署于企业内网；服务端部署于公网。
proxy启动后，在两个端口提供服务，一个端口扮演的是普通的http 代理服务器的角色；另一个端口则是一个隧道角色，隧道接受agent的链接。
启动时的示例如下:(假设在一台公网域名为tunnel.12zan.net的机器上运行)
proxy -secure -tunnelAddr=0.0.0.0:4433 -proxyAddr=:1997
agent启动后向服务端发起一定数量的tcp socket链接，并向服务端汇报，自己为哪个地址的http请求提供服务。连接成功后即向服务器发送一个SIGN {ID} {ADDRESS}的登入串，标明自己的声份。这样Proxy端在接收到这个address地址的proxy请求就可以由这个链接来完成。

例如:
agent -secure -backendAddr=inner.app:8080 -tunnelAddr=tunnel.12zan.net:4433

这个agent 在启动后，就会连接tunnel.12zan.net的4433端口，并汇报说自己提供的是到inner.app:8080的内网服务。

在调用内网接口时，指定http_proxy为tunnel.12zan.net:1997,如下图所示:


```$xslt
http_proxy=tunnel.12zan.net:1997 curl http://inner.app:8080/
```


proxy 在解析了当前http请求是要请求哪个http地址后，将请求写入到对应的tunnel的tcp链接上，并从对应链接中读取数据返回给客户端。
而在agent端，从跟公网服务器的tunnel链接中取到数据则发给内网的web应用，并取得返回，写入到公网服务器的tunnel链接中。 


目前还不够稳定，遇到有504的情况，需要重试一下。