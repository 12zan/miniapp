//app.js
const Weet = require("./weet2/weet.js");
const update = wx.getUpdateManager();
// https://beauty.z.12zan.net
// http://192.168.2.47:8807/api/
// http://192.168.1.47:8807
App({
    'appId': 'wxe6cc86baa5fed7f5',
    "domain": "https://beauty.z.12zan.net",
    'version': '1.0.2',
    onLaunch: function(options) {
        // 展示本地存储能力
        if (options.scene == 1011 || options.scene == 1047){
            wx.setStorageSync('account',true)
        }else{
            wx.setStorageSync('account', false)
        }
        var logs = wx.getStorageSync('logs') || []
        logs.unshift(Date.now())
       // 登录
        Weet.event.onLaunch.call(this, options);
        wx.removeStorage({
            key: 'error',
            success: function(res) {},
        })
        wx.getExtConfig({
            success: res => {
                if (!res.extConfig.appId) {
                    wx.showModal({
                        title: 'ERROR',
                        content: 'NO HAVE EXT',
                    })
                }
                this.appId = res.extConfig.appId;
                this.domain = res.extConfig.domain + '/api/';
                this.name = res.extConfig.name;
                this.themeColor = res.extConfig.themeColor;
                this.tabBar = res.extConfig.tabBar;
                this.sid = res.extConfig.sid;
                Weet.misc.login();
               
            }
        })
    },
    onShow: function(options) {         
        update.onUpdateReady(function() {
            wx.showModal({
                title: '更新提示',
                content: '新版本已经准备好，是否重启应用？',
                success: function(res) {
                    if (res.confirm) {
                        update.applyUpdate()
                    }
                }
            })

        })
    },
    globalData: {
        userInfo: null,
        token: '',
    }
})