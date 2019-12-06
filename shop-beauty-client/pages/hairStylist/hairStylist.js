// pages/HairStylist/HairStylist.js
const app = getApp();
const weet = require('../../weet2/weet.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
      page: 1,
      totalPage: '', //总页数
      list:[],
      isShare:false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
      if(JSON.stringify(options) != '{}'){
          this.setData({
              options:options
          })
      }
      weet.login(() => {
          weet.event.onLoad.call(app, this, this.data.options);
      })
      wx.getSetting({
          success: function (res) {
              if (!res.authSetting['scope.userInfo']) {
                  wx.redirectTo({
                      url: '../authorization/authorization'
                  })
              }
          }
      })
     if(JSON.stringify(options) != '{}'){
         this.setData({
             isShare:true
         })
     }
  },
  toIndex: function(){
     wx.reLaunch({
         url: '../index/index',
     })
  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
      weet.login(() => {
          weet.event.onLoad.call(app, this, this.data.options);
      })
      let self = this
      let shopInfo = wx.getStorageSync('shopInfo')
      self.setData({
          shopInfo: shopInfo
      })
      weet.zan.get('staffs?page=1',{}).then(res=>{
          self.setData({
              list: res.data.data,
              totalPage: res.data.page.totalPage
          })
      })
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
  
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
      var self = this;
      // 当前页+1
      var page = self.data.page + 1;
      self.setData({
          page: page,
      })

      if (page <= self.data.totalPage) {
          wx.showLoading({
              title: '加载中',
          })
          // 请求后台，获取下一页的数据。
          weet.zan.get('staffs?page=' + self.data.page, {}).then(res => {
              wx.hideLoading()
              if(res.statusCode == 200){
                  self.setData({
                      list: self.data.list.concat(res.data.data)
                  })
                
              }
          })

      }
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
      let self = this
      return {
          title: self.data.shopInfo.name,
          path: '/pages/authorization/authorization?isShare=2&scene=' + app.globalData.encOpenId
      }
  }
})