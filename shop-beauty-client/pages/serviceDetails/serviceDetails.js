// pages/serviceDetails/serviceDetails.js
const app = getApp();
const weet = require('../../weet2/weet.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        detailsData: [],
        id: '',
        indicatorDots: true,
        autoplay: true,
        interval: 5000,
        duration: 1000,
        isShare: false,
        indicatoractivecolor: '#fff'
    },
    toIndex: function() {
        wx.switchTab({
            url: '../index/index',
        })
    },
    //预览图片
    previewImage: function(e) {
        let self = this
        let current = e.target.dataset.url;
        let urls = []
        for (let i in self.data.detailsData.banners) {
            urls.push(self.data.detailsData.banners[i].path)
        }
        wx.previewImage({
            current: current, // 当前显示图片的http链接
            urls: urls // 需要预览的图片http链接列表
        })
    },
    clickMe: function(e) {
        let urls = []
        let current = e.target.dataset.url.path;
        this.data.detailsData.introduce.forEach(function(item, index) {
            urls.push(item.path)
        })
        wx.previewImage({
            current: current, // 当前显示图片的http链接
            urls: urls // 需要预览的图片http链接列表
        })
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
        let self = this
        if (JSON.stringify(options) !== '{}') {
            self.setData({
                id: options.id
            })
            if (options.isShare != undefined) {
                self.setData({
                    id: options.id,
                    isShare: true
                })
            }

            weet.zan.get('preload', {}).then(res => {
                if (res.statusCode == 200) {
                    if (res.data.data.member.level == 0) {
                        self.setData({
                            isVip: false
                        })
                    }
                    if (res.data.data.member.level != 0) {
                        self.setData({
                            isVip: true
                        })
                    }
                }
            })
            weet.zan.get('items/' + options.id, {}).then(res => {
                if (res.statusCode == 200) {
                    self.setData({
                        detailsData: res.data.data
                    })
                    let paths = []
                    res.data.data.introduce.forEach(function(item,index){
                        paths.push(item.path)
                    })
                    self.setData({
                        paths: paths
                    })

                } else if (res.statusCode == 404) {
                    wx.showModal({
                        content: '该服务已删除，无法查看！',
                        showCancel: false,
                        confirmText: '确定',
                        success: function(res) {
                            if (res.confirm) {
                                wx.reLaunch({
                                    url: '../index/index',
                                })
                            }
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
        }
    },
    toSettlement: function() {
        let self = this
        if (self.data.detailsData.status == 0) {
            wx.showModal({
                title: '提示',
                content: '该服务已下架，无法购买。',
                showCancel: false,
                confirmText: '确定',
                success: function() {

                }
            })
        } else if (self.data.isVip == false) {
            wx.showModal({
                title: '提示',
                content: '你当前为非会员，只要充值任意一笔金额后即可升级为会员，享受会员价，是否去充值？',
                showCancel: true,
                confirmText: '去充值',
                cancelText: '继续购买',
                success: function(res) {
                    if (res.confirm) {
                        wx.navigateTo({
                            url: '../recharge/recharge',
                        })
                    }
                    if (res.cancel) {
                        wx.navigateTo({
                            url: '../settlement/settlement?id=' + self.data.id,
                        })
                    }

                },
            })
        } else {
            wx.navigateTo({
                url: '../settlement/settlement?id=' + self.data.id,
            })
        }


    },
    formId: function(e) {
        let formId = e.detail.formId;
        weet.zan.post('save-formid', {
            formId: formId
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

        wx.getSetting({
            success: function(res) {
                if (!res.authSetting['scope.userInfo']) {
                    wx.redirectTo({
                        url: '../authorization/authorization'
                    })
                }
            }
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
            path: '/pages/authorization/authorization?isShare=4&id=' + this.data.id + '&scene=' + app.globalData.encOpenId
        }
    }
})