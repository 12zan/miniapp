const login = require('../lib/misc.js');

function request(method, url, data) {
    return new Promise((resolve, reject) => {
        var version = getApp().version
        wx.request({
            url: getApp().domain + url,
            data: data,
            method: method,
            // 自动补充header里的token值
            header: {
                'Accept': 'application/json',
                'Authorization': getApp().globalData.token,
                'version': version,
                'appid':getApp().appId
            },
            success: function (res) {
                // 当token已过期，更新token后重新执行该函数
                if (res.statusCode === 401 || res.statusCode === 504) {
                    login.login(() => {
                        resolve(request(method, url, data))
                    })
                } else {
                    resolve(res);
                }
            },
            fail: function (res) {
                reject(res);
            }
        })
    })
}
let get = function (url) {
    return this.request('GET', url);
};
let post = function (url, data) {
    return this.request('POST', url, data);
}

module.exports = {
    request, get, post
};