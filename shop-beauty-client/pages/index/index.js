// pages/index/index.js
const app = getApp();
const weet = require('../../weet2/weet.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        isAddress:'',
        account:false,
        array:[],
        indexData:[],
        isPriceList:1,
        isServiceList:1,
        swiperCurrent: 1,
        tel:'',
        indicatorDots: true,
        autoplay: true,
        interval: 4000,
        duration: 1000,
        indicatorColor: '#fff',
    },
    getUserInfo: function (e) {
        let self = this;
        let openSetting = function () {
            if (e.detail.userInfo) {
                wx.setStorageSync('userInfo', e.detail.userInfo)
                self.setData({
                    userInfo: e.detail.userInfo
                })
                weet.zan.post('fix-info', {
                    userInfo: e.detail.userInfo
                }).then(res => {
                })
            } 
        }
        openSetting();
    },
    toMap: function (){
      wx.navigateTo({
          url: '../map/map',
      })
    },
    toLoading: function (){
        this.setData({
            xixi: 1,
            isPriceList:1
        })
    },
    loading: function(){
      this.setData({
          haha:1,
          isServiceList:1
      })
    },
    //预览图片
    previewImage: function (e) {
        let self = this
        let current = e.target.dataset.url;
        let urls = []
        for (let i in self.data.indexData.banner){
            urls.push(self.data.indexData.banner[i].image.path)
        }
        wx.previewImage({
            current: current, // 当前显示图片的http链接
            urls: urls // 需要预览的图片http链接列表
        })
    },
    // 去服务详情
    toServiceDetails: function (e){
        wx.navigateTo({
            url: '../serviceDetails/serviceDetails?id=' + e.currentTarget.dataset.id,
        })
    },
    // 拨打电话
    toCall: function (e){
        wx.makePhoneCall({
            phoneNumber: e.currentTarget.dataset.tel  //仅为示例，并非真实的电话号码
        })
    },
    // 去发型师列表
    toHairStylist: function(){
        wx.navigateTo({
            url: '../hairStylist/hairStylist'
        })
    },
    // 去订单页
    toOrder: function (){
        wx.navigateTo({
            url: '../order/order'
        })
    },
    // 去我的页
    toMy: function () {
        wx.navigateTo({
            url: '../my/my',
        })
    },
    // 轮播点
    clickMe: function(e){
        this.setData({
            swiperCurrent: e.detail.current
        })
    },
    // 去预约
    toAppointment:function(){
        wx.navigateTo({
            url: '../appointment/appointment'
        })
    },
    // 商家页
    toBusiness:function (){
        wx.navigateTo({
            url: '../business/business'
        })
    },
    bindscrolltolower:function (){
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
    },
    getData: function (){
        let self = this
        weet.zan.get('preload', {}).then(res => {
            self.setData({
                isStaff: res.data.data.isStaff,
                vip: res.data.data.member,
                isOpenStaffOrder: res.data.data.shopInfo.isOpenStaffOrder
            })
        })
        weet.zan.get('home/show', {}).then(res => {
            self.setData({
                indexData: res.data.data
            })
            if (res.data.data.shopInfo.lat == null || res.data.data.shopInfo.lng == null || (res.data.data.shopInfo.lat == null && res.data.data.shopInfo.lng == null)) {
                self.setData({
                    isAddress: 1 //1无效地址 2有效地址
                })
            
            }else{
                self.setData({
                    isAddress: 2 //1无效地址 2有效地址
                }) 
            }


            wx.setStorageSync('shopInfo', res.data.data.shopInfo)
            if (res.data.data.priceList.length > 6) {
                self.setData({
                    isPriceList: 2
                })
            }
            if (res.data.data.priceList.length <= 6) {
                self.setData({
                    isPriceList: 1
                })
            }
            if (res.data.data.services.length >6) {
                self.setData({
                    isServiceList: 2
                })
            }

            if (res.data.data.services.length <= 6) {
                self.setData({
                    isServiceList: 1,
                })
            }
            let servicesList = []
            let priceList = []
            res.data.data.priceList.forEach(function (item, index) {
                if (index < 6) {
                    priceList.push(item)
                }
            })
            res.data.data.services.forEach(function (item, index) {
                if (index < 6) {
                    servicesList.push(item)
                }
            })
            self.setData({
                servicesList: servicesList,
                priceList: priceList
            })
            let array = []
            let priceArray=[]
            res.data.data.services.forEach(function (item, index) {
                if (index >= 6) {
                    array.push(item)
                }
            })
            res.data.data.priceList.forEach(function (item, index) {
                if (index >= 6) {
                    priceArray.push(item)
                }
            })
            self.setData({
                array: array,
                priceArray: priceArray
            })
        })
    },
    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function() {
        weet.login(() => {
            weet.event.onLoad.call(app, this, this.data.options);
        })
        let self = this;
        let account = wx.getStorageSync('account')
       self.setData({
           account: account,
           version: app.version
       })
        wx.getSetting({
            success: function (res) {
                if (!res.authSetting['scope.userInfo']) {
                    wx.redirectTo({
                        url: '../authorization/authorization',
                    })
                }else{
                    self.getData()
                }
            },
        })
        wx.getStorage({
            key: 'userInfo',
            success: function (res) {
                self.setData({ userInfo: res.data })
            },
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
       this.getData()
       wx.stopPullDownRefresh()
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
            title: self.data.indexData.shopInfo.name,
            path: '/pages/authorization/authorization?isShare=1&scene=' + app.globalData.encOpenId
        }
    }
})