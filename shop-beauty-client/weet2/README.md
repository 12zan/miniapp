Weet2 是杭州猿力科技的小程序开发工具包。

包含的功能有:

1. 通用的登陆授权及用户未授权的处理;
2. 一个统一的图片裁切页面；
3. 弹出浮层的js处理；
4. 上传文件及发送wx.request 提供了一个 promise的版本；

# 使用方法及统一规范

1. 要求小程序的允许访问域名有t.z.12zan.net,t.yuanfenxi.com ;
2. 我们要求app.js 采用标准的方法来初始化，每款小程序，只需要定制自己的sid,appId及服务器域名等字段就行了。

```javascript
let baseConfig = require("./weet/baseConfig.js");
let config = baseConfig.extend(baseConfig,{
    name: 'happycard',
    domain: "https://happycard.z.12zan.net/happy/",
    sid: "0e6f2457bb3f9a50210b55396d6d5a6e",
    appId:"wxebe131f8415d8bec",
    version:"1.0.0",
    afterAppEntry:(options)=>{
        console.log("just app launched");
    }
});
App(config);
```

如果不能修改app.js的话，请至少修改App里OnLaunch方法:
```javascript
App({
    onLaunch: function(options) {
        Weet.event.onLaunch.call(this,options);
        this.afterAppEntry(options);
    }
});
```

2. 要求所有的Page的onLoad方法要加载标准的方法
```javascript
const app = getApp();
const Weet = require("../../weet/weet.js");
Page({
    data: {
        userInfo: null,
        noUser: true,
    },
    onLoad: function (options) {
       // return;
        let self = this;      
        Weet.event.onLoad.call(app,this,options);
    }
});
```

3. 完成登陆后:
```javascript
const app = getApp();
const Weet = require("../../weet/weet.js");
    //... 在登陆成功后的代码里加入;
    let encodeOpenId =  Weet.md5(openId);
    Weet.event.onLogin(app,encodeOpenId);
```


4. 记录跳出小程序事件:


# 已知事件列表

- onLoad
- onLaunch
- onLogin
- jump
- jumpSucc
- jumpFail 

# 部分功能说明

### 函数调用

调用weet中提供的函数时，需要注意，misc.js里提供的，都是需要将this变量绑定为app对象的。

### 关于登陆
对于登陆，目前有这么几个要点:
1. 在需要用户登录的时候，才需要去调用app.login;根据数据记录，很多
2. 用户授权成功后，会将openId和对应的用户信息存在服务器上；将用户资料及openId存放在localStorage里；下次再需要login的时候，会直接从localstorage里取；
3. 如果用户点了拒绝授权，则会提示用户这一步是需要用户授权的，请用户到设置页面进行操作。
4. app.login接受一个callback函数做为参数，用户授权成功时，会调用这个cb;大多数页面需要根据用户信息初始化一些数据时，是写在这里的。

