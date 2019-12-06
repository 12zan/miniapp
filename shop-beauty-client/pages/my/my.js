// pages/my/my.js
const app = getApp();
const weet = require('../../weet2/weet.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {

    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        weet.event.onLoad.call(app, this, options);
    },
    // 商家页
    toBusiness: function () {
        wx.navigateTo({
            url: '../business/business'
        })
    },
    toRecharge: function() {
        wx.navigateTo({
            url: '../recharge/recharge',
        })
    },
    toQRcode: function() {
        wx.navigateTo({
            url: '../qrCode/qrCode',
        })
    },
    toBalanceList: function() {
        wx.navigateTo({
            url: '../balanceList/balanceList',
        })
    },
    onPageScroll: function(){
    },
    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function() {
        wx.getSetting({
            success: function (res) {
                if (!res.authSetting['scope.userInfo']) {
                    wx.redirectTo({
                        url: '../authorization/authorization'
                    })
                }
            }
        })
        let shopInfo = wx.getStorageSync('shopInfo')
        let userInfo = wx.getStorageSync('userInfo')
        let self = this
        weet.zan.get('preload', {}).then(res => {
            if (res.statusCode == 200) {
                self.setData({
                    isStaff: res.data.data.isStaff,
                    vip: res.data.data.member,
                    shopInfo: res.data.data.shopInfo,
                })
            }
        })
        self.setData({
            shopInfo: shopInfo,
            userInfo: userInfo
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
        let self = this
        wx.showNavigationBarLoading()
        weet.zan.get('preload', {}).then(res => {
            if (res.statusCode == 200) {
                self.setData({
                    isStaff: res.data.data.isStaff,
                    vip: res.data.data.member,
                    shopInfo: res.data.data.shopInfo
                })
                wx.stopPullDownRefresh()
                wx.hideNavigationBarLoading()
            }else{
                wx.showModal({
                    title: '提示',
                    content: res.data.msg,
                    showCancel:false
                })
            }
        })
    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function() {

    },

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function(res) {
        let self = this
        if (res.from === 'button') {
            return {
                title: self.data.shopInfo.name,
                imageUrl: self.data.shopInfo.logo,
                path: '/pages/authorization/authorization?isShare=5&scene=' + app.globalData.encOpenId
            }
        }
        return {
            title:self.data.shopInfo.name,
            imageUrl: self.data.shopInfo.logo,
            path: '/pages/authorization/authorization?isShare=5&scene=' + app.globalData.encOpenId 
        }
    }
})