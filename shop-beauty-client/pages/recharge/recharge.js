// pages/recharge/recharge.js
const app = getApp();
const weet = require('../../weet2/weet.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        money: '',
        isShare: false
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        if (JSON.stringify(options) != '{}') {
            this.setData({
                options: options
            })
        }
        weet.login(() => {
            weet.event.onLoad.call(app, this, options);
        })
        wx.getSetting({
            success: function(res) {
                if (!res.authSetting['scope.userInfo']) {
                    wx.redirectTo({
                        url: '../authorization/authorization'
                    })
                }
            }
        })
        let self = this
        if (JSON.stringify(options) != '{}') {
            self.setData({
                isShare: true
            })
        }
        weet.zan.get('preload', {}).then(res => {
            if (res.statusCode == 200) {
                self.setData({
                    rechargeMin: res.data.data.shopInfo.rechargeMin
                })
            }
        })
        weet.zan.get('activity/store-value', {}).then(res => {
            if (res.statusCode == 200) {
                self.setData({
                    list: res.data.data
                })
            }
        })
    },
    toIndex: function() {
        wx.reLaunch({
            url: '../index/index',
        })
    },
    bindblur: function(e) {
        this.setData({
            money: e.detail.value
        })
    },

    bindReplaceInput: function(e) {
        this.setData({
            money: e.detail.value,
        })
    },
    toRecharge: function() {
        let self = this
        wx.showLoading({
            title: '加载中',
            mask: true
        })
        if (!self.data.money) {
            wx.showModal({
                title: '提示',
                content: '充值金额不能为空',
                showCancel: false,
            })
            wx.hideLoading()
            return
        }
        if (self.data.money == 0) {
            wx.showModal({
                title: '提示',
                content: '请输入正确的充值金额！',
                showCancel: false,
            })
            wx.hideLoading()
            return
        }
        if ((self.data.money).split('.')[1]){
            if ((self.data.money).split('.').length > 2) {
                wx.showModal({
                    title: '提示',
                    content: '请输入正确的充值金额！',
                    showCancel: false,
                })
                wx.hideLoading()
                return
            }

            if ((self.data.money).split('.')[1].length > 2) {
                wx.showModal({
                    title: '提示',
                    content: '请输入正确的充值金额！',
                    showCancel: false,
                })
                wx.hideLoading()
                return
            }
        }
       
        if (self.data.money == 0 || self.data.money == '0.00' || self.data.money == '00' || self.data.money == '000' || self.data.money == '0000' || self.data.money == '00000' || self.data.money == '000000' || self.data.money == '0000000' || self.data.money == '00000000') {
            wx.showModal({
                title: '提示',
                content: '请输入正确的充值金额！',
                showCancel: false,
            })
            wx.hideLoading()
            return
        }
        if (self.data.money < (self.data.rechargeMin / 100).toFixed(2)) {
            wx.showModal({
                content: '最低充值金额: ¥' + (self.data.rechargeMin / 100).toFixed(2) + '\r\n' + '你输入的金额低于最低充值金额，请重新输入！',
                showCancel: false,
            })
            wx.hideLoading()
            return
        }
        weet.zan.post('orders/store-value', {
            money: self.data.money
        }).then(res => {
            if (res.statusCode == 200) {

                self.setData({
                    orderId: res.data.data.orderId
                })
                weet.zan.post('pay/wx', {
                    orderId: res.data.data.orderId
                }).then(res => {
                    wx.hideLoading()
                    if (res.statusCode == 200) {
                        wx.requestPayment({
                            'timeStamp': res.data.timestamp,
                            'nonceStr': res.data.nonce_str,
                            'package': 'prepay_id=' + res.data.prepay_id,
                            'signType': 'MD5',
                            'paySign': res.data.sign,
                            'success': function(res) {
                                wx.redirectTo({
                                    url: '../rechargeRes/rechargeRes?payRes=1&orderId=' + self.data.orderId + '&totalPrice=' + self.data.money + '&type=wx',
                                })
                            },
                            'fail': function(res) {
                                wx.navigateTo({
                                    url: '../rechargeRes/rechargeRes?payRes=2&isNoMoney=0&type=wx',
                                })
                            }
                        })
                    } else {
                        wx.showModal({
                            title: '提示',
                            content: res.data.msg,
                            showCancel: false
                        })
                    }

                })
            } else {
                wx.showModal({
                    title: '提示',
                    content: res.data.msg,
                    showCancel: false
                })
            }

        })

    },
    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function() {
        weet.login(() => {
            weet.event.onLoad.call(app, this, this.data.options);
        })
        let shopInfo = wx.getStorageSync('shopInfo')
        this.setData({
            shopInfo: shopInfo
        })
    },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function() {

    },

    /**
     * 生命周期函数--监听页面隐藏
     */
    onHide: function() {

    },

    /**
     * 生命周期函数--监听页面卸载
     */
    onUnload: function() {

    },

    /**
     * 页面相关事件处理函数--监听用户下拉动作
     */
    onPullDownRefresh: function() {

    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function() {

    },

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function() {
        let self = this
        return {
            title: self.data.shopInfo.name,
            path: '/pages/authorization/authorization?isShare=6&scene=' + app.globalData.encOpenId
        }
    }
})