// pages/pay/pay.js
const app = getApp();
const weet = require('../../weet2/weet.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        tips: '',
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        let self = this
        wx.getSetting({
            success: function (res) {
                if (!res.authSetting['scope.userInfo']) {
                    wx.redirectTo({
                        url: '../authorization/authorization'
                    })
                }
            }
        })
        if (JSON.stringify(options) !== '{}') {
            if (options.payRes == 1) {
                weet.zan.get('orders/' + options.orderId + '/find', {}).then(res => {
                    if (options.type == "wx") {
                        if (res.statusCode == 200) {
                            self.setData({
                                payRes: 1,
                                totalPrice: options.totalPrice,
                                orderId: options.orderId
                            })
                        } else {
                            self.setData({
                                payRes: 2,
                                tips: res.data.msg
                            })
                        }
                    }

                    if (options.type == "store") {
                        if (options.type == "store" && options.payRes == 1) {
                            self.setData({
                                payRes: 1,
                                totalPrice: options.totalPrice,
                                orderId: options.orderId
                            })
                        }
                        if (options.type == "store" && options.payRes == 2) {
                            self.setData({
                                payRes: 2,
                                tips: '余额不足，请先充值'
                            })
                        }
                    }
                })
            }else{
                self.setData({
                    payRes: 2,
                    tips: '支付失败，请重新支付！'
                })
            }
        }
    },
    toOrderDetails: function() {
        let self = this
        wx.navigateTo({
            url: '../orderDetails/orderDetails?orderId=' + self.data.orderId,
        })
    },
    toPre: function() {
        wx.navigateBack({
            delta: 2
        })
    },
    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function() {

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


})