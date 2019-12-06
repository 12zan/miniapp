// pages/balanceList/balanceList.js
const app = getApp();
const weet = require('../../weet2/weet.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        list: [],
        page:1,
        totalPages:''
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
        weet.zan.get('preload',{}).then(res=>{
            if (res.statusCode == 200){
                self.setData({
                    vip: res.data.data.member,
                    ['vip.money']: (res.data.data.member.money).toFixed(2)
                })
            }
        })
        weet.zan.get('member/money-logs?page=1', {}).then(res => {
            if (res.statusCode == 200) {
                res.data.data.forEach((item, index) => {
                        item.money = (item.money).toFixed(2)
                       
                })
                self.setData({
                    list: res.data.data,
                    totalPage: res.data.page.totalPage
                })
            }
        })
    },
    toBalanceDetails: function(e) {
        wx.navigateTo({
            url: '../balanceDetails/balanceDetails?id=' + e.currentTarget.dataset.id,
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
        let self = this;
        // 当前页+1
        let page = self.data.page + 1;

        self.setData({
            page: page,
        })

        if (page <= self.data.totalPage) {
            wx.showLoading({
                title: '加载中',
            })
            // 请求后台，获取下一页的数据。
            weet.zan.get('member/money-logs?page=' + self.data.page, {}).then(res => {
                if (res.statusCode == 200) {
                    wx.hideLoading()
                
                    res.data.data.forEach((item, index) => {
                            item.money = (item.money).toFixed(2)
                    })
                    self.setData({
                        list: self.data.list.concat(res.data.data)
                    })
                } else {
                    wx.showModal({
                        title: '提示',
                        content: res.data.msg,
                        showCancel: false,

                    })
                }


            })

        }
    },


})