const Weet = require("./weet.js");
const update = wx.getUpdateManager();
module.exports = {
    name: 'some',
    sid: "some",
    version: "1.0.0",
    extend: (self, baseConfig) => {
        for (let i in baseConfig) {
            self[i] = baseConfig[i];
        }
        return self;
    },
    globalData: {
        userInfo: null,
        openId: '',
        system: {
            height: 667,
            width: 375
        },
    },
    consoleLog: function (category, stack) {
        Weet.consoleLog.call(this, category, stack);
    },
    log: function (category, stack) {
        Weet.consoleLog.call(this, category, stack);
    },
    generateUIDNotMoreThan1million: Weet.generateUIDNotMoreThan1million,
    onError: function (args) {
        try {
            Weet.onError.call(this, args, "happycard");
        } catch (e) { }
    },
    reportEvent: function (event, id) {
        try {
            Weet.reportEvent.call(this, event, id);
        } catch (e) { }
    },
    // login: function (cb) {
    //     Weet.login.call(this, cb);
    // },

    onLaunch: function (options) {
        Weet.event.onLaunch.call(this, options);
        // console.log("this in onLaunch:");
        // console.log(this);
        //this.afterAppEntry(this,options);
        wx.removeStorage({
            key: 'error',
            success: function (res) { },
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
                this.name = res.extConfig.name;
                this.domain = res.extConfig.domain;
                this.theme = res.extConfig.theme;
                this.themeColor = res.extConfig.themeColor;
                this.tabBar = res.extConfig.tabBar;
                this.sid = res.extConfig.sid;
                Weet.misc.login();
            }
        })
    },
    onShow: function () {
        update.onUpdateReady(function () {
            wx.showModal({
                title: '更新提示',
                content: '新版本已经准备好，是否重启应用？',
                success: function (res) {
                    if (res.confirm) {
                        update.applyUpdate()
                    }
                }
            })

        })
    },
    onPageNotFound: function (options) {

    },
    setCache: function (key, value) {
        wx.setStorageSync(this.version + "_" + key, value);
    },
    readCache: function (key) {
        return wx.getStorageSync(this.version + "_" + key);
    },
    zanRequest: require("./lib/zan.js")
};