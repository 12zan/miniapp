// pages/orderDetails/orderDetails.js
const app = getApp();
const weet = require('../../weet2/weet.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
      details:[],
      currentTab:0,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
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
     if(JSON.stringify(options)!== '{}'){
         self.setData({
             orderId: options.orderId
         })
         self.getDatas()
        
     }
     
  },
    getDatas: function () {
        let self = this
        wx.showLoading({
            title: '加载中',
        })
        weet.zan.get('orders/' + self.data.orderId, {}).then(res => {
            if (res.statusCode == 200) {
                wx.hideLoading()
                self.setData({
                    details: res.data.data
                })
            }
        })
    },
    tabLeft: function () {
        this.setData({
            currentTab: 0
        })
    },
    tabRight: function (){
        this.setData({
            currentTab:1
        })
    },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
  
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
  
  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {
  
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {
      this.getDatas()
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
  
  },

})