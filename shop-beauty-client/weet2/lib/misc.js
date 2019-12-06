const XWrapper = require("./wxwrapper");
const md5 = require("./md5.js");

/**
 * 注意，需要将this绑定为App对象;
 * @param args
 * @param domain
 */
const onError = function (args, domain) {
    let array = args.split("\n");
    let message = this.name + ":" + array.shift();

    let stack = array.join("\n");

    let info = wx.getSystemInfoSync();
    let uid = "";
    try {
        uid = this.globalData.openId;
    } catch (e) {

    }
    let src = "https://t.yuanfenxi.com/stat/error.gif?_yfx_ga="
        + uid +
        "&os=" + info.platform
        + "&browser=" + info.brand + ":" + info.model + "&sid=" + this.sid + "&stack=" + encodeURIComponent(stack.slice(0, 1000)) + "&rn=" + Math.random() + "-" + Math.random() + "-" + Math.random() + "&message=" + encodeURIComponent(message.slice(0, 1000)) + "&domain=" + domain;
    wx.request({
        url: src,
    });
};
/**
 * 记录一个变量，或是一条消息;
 * @param category
 * @param stack
 * */
const consoleLog = function (category, stack) {
    let self = this;
    console.log(category);
    console.log(stack);
    let message = self.name + ":" + category;
    let info = wx.getSystemInfoSync();
    let uid = "";
    try {
        uid = this.globalData.openId;
    } catch (e) {

    }
    let msg = "";
    try {
        if ((typeof stack) === "string") {
            msg = stack;
        } else {
            msg = JSON.stringify(stack);
        }
    } catch (e) {

    }
    msg = msg || "";
    let src = "https://t.yuanfenxi.com/stat/error.gif?_yfx_ga="
        + uid +
        "&os=" + info.platform
        + "&browser=" + info.brand + ":" + info.model + "&sid=" + this.sid + "&stack=" + encodeURIComponent(msg.slice(0, 1000)) + "&rn=" + Math.random() + "-" + Math.random() + "-" + Math.random() + "&message=" + encodeURIComponent(message.slice(0, 1000)) + "&domain=" + self.name;
    wx.request({
        url: src,
    });
};
const reportEvent = function (app, page, eventType, eventData, eventDataNum) {
    let self = this;
    let prefix = "https://t.z.12zan.net/v1/event";
    let info = wx.getSystemInfoSync();
    let rn = page.rn || "";
    let options = page.options || {};
    let pageId = page.pageId || "";
    let pageUrl = page.pageUrl || "";
    let encOpenId = getApp().globalData.encOpenId || 'noLogin';
    if(encOpenId == 'noLogin'){
        setTimeout(()=>{
            self.login(self.reportEvent(app, page, eventType, eventData, eventDataNum));
        },2000)
        return
    }

    let scene = "";
    let externalSource = "";
    if (
        ((typeof options.scene) === "string")
        && (options.scene.length === 32)
    ) {
        scene = options.scene.substr(-8);
    }
    else if (
        options.query && (typeof options.query._yfx_s) === "string" && options.query._yfx_s.length > 0
    ) {
        externalSource = options.query._yfx_s;
    }
    let src = prefix + "?_yfx_ga=" + app.globalData.userId + "&appId=" + app.appId + "&eventType=" + eventType + "&pageId=" + encodeURIComponent(pageId) + "&pageUrl=" + encodeURIComponent(pageUrl) + "&scene=" + scene + "&source=" + externalSource + "&windowHeight=" + info.windowHeight + "&windowWidth=" + info.windowWidth + "&screenWidth=" + info.screenWidth + "&screenHeight=" + info.screenHeight + "&version=" + info.version + "&system=" + info.system + "&pixelRatio=" + info.pixelRatio + "&model=" + info.model + "&language=" + info.language + "&fontSizeSetting=" + info.fontSizeSetting + "&brand=" + info.brand + "&batteryLevel=" + info.batteryLevel + "&sdkVersion=" + info.SDKVersion + "&rn=" + encodeURIComponent(rn) + "&sid=" + app.sid + "&platform=" + info.platform + "&eventData=" + encodeURIComponent(eventData) + "&eventDataNum=" + encodeURIComponent(eventDataNum) + "&encOpenId=" + encOpenId;
    wx.request({
        url: src,
        fail: (err) => {
            console.log("请求出错了:");
            console.log(err);
        }
    });
};


const switchPopUp = function (currentStatus) {
    /* 动画部分 */
    // 第1步：创建动画实例
    let animation = wx.createAnimation({
        duration: 200,  //动画时长
        timingFunction: "linear", //线性
        delay: 0  //0则不延迟
    });

    // 第2步：这个动画实例赋给当前的动画实例
    this.animation = animation;

    // 第3步：执行第一组动画
    animation.opacity(0).rotateX(-100).step();

    // 第4步：导出动画对象赋给数据对象储存
    this.setData({
        animationData: animation.export()
    });

    // 第5步：设置定时器到指定时候后，执行第二组动画
    setTimeout(function () {
        // 执行第二组动画
        animation.opacity(1).rotateX(0).step();
        // 给数据对象储存的第一组动画，更替为执行完第二组动画的动画对象
        this.setData({
            animationData: animation
        });

        //关闭
        if (currentStatus == "close") {
            this.setData(
                {
                    showModalStatus: false
                }
            );
        }
    }.bind(this), 200)

    // 显示
    if (currentStatus == "open") {
        this.setData(
            {
                showModalStatus: true
            }
        );
    }
};

/**
 * 需要绑定到app上;
 */
const launch = function () {
    let self = this;
    wx.getSystemInfo({
        success: function (res) {
            if (res.errMsg === "getSystemInfo:ok") {
                self.globalData.system = {
                    height: res.screenHeight,
                    width: res.screenWidth,
                };
                self.globalData.scrollHeight = res.windowHeight
            }
        }
    });
    let info = self.readCache("userInfo");
    if (info) {
        self.globalData.userInfo = info;
    }
    let openId = self.readCache("openId");
    if (openId) {
        self.globalData.openId = openId;
    }
    let encOpenId = self.readCache("encOpenId");
    if (encOpenId) {
        self.globalData.encOpenId = encOpenId;
    }
    let userId = self.readCache("userId");
    if (userId) {
        self.globalData.userId = userId;
    } else {
        userId = md5(Math.random() + new Date());
        self.globalData.userId = userId;
        self.setCache("userId", userId);
    }
    login.call(self);
};

const afterAppEntry = (app) => {
};
const getPage = () => {
    let page = {};
    try {
        let pages = (getCurrentPages());
        page = pages[pages.length - 1];
    } catch (e) {

    }
    return page;
};
const jump = (options) => {
    let app = getApp();
    let page = getPage();
    console.log("jump");
    reportEvent(app, page, "jump", options.appId, 0);
    wx.navigateToMiniProgram({
        appId: options.appId,
        path: options.path,
        extraData: options.extraData,
        success(res) {
            console.log("jump succ");
            reportEvent(app, page, "jumpSucc", options.appId, 0);
            // 打开成功
        },
        fail(res) {
            console.log("jumpfailed");
            reportEvent(app, page, "jumpFail", options.appId, 0);
        }
    })

};
const login = function (cb) {
    let app = getApp();
    let domain = app.domain;
    wx.login({
        success: res => {
            wx.request({
                url: domain + 'auth/login',
                method: 'POST',
                data: {
                    'code': res.code,
                    'appid': app.appId
                },
                success: res => {
                    if (res.statusCode === 200) {
                        app.globalData.token = res.data.data.token_type + ' ' + res.data.data.access_token;
                        app.globalData.encOpenId = res.data.data.encOpenId;
                        onLogin(app, res.data.data.encOpenId);
                        if ((typeof cb) === 'function') {
                            cb();
                        }
                    } else {
                        wx.showModal({
                            title: '提示',
                            content: res.data.msg,
                            showCancel: false
                        })
                    }
                },
                fail:res=>{
                    console.log(wx.login)
                    console.log(res)
                }
            })
        }
    })
};
const onLogin = function (app, encOpenId) {
    let page = {};
    try {
        let pages = (getCurrentPages());
        if (pages.length > 1) {
            page = pages[pages.length - 1];
        }
    } catch (e) {
    }
    app.globalData.encOpenId = encOpenId;
    reportEvent(app, page, "onLogin", "", 0);
};
module.exports = {
    onError: onError,
    reportEvent: reportEvent,
    switchPopUp: switchPopUp,
    launch: launch,
    login: login,
    consoleLog: consoleLog,
    afterAppEntry: afterAppEntry,
    jump: jump
};