// pages/balanceDetails/balanceDetails.js
const app = getApp();
const weet = require('../../weet2/weet.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
  balanceDetails:[]
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
     if(JSON.stringify(options)!= '{}'){
         weet.zan.get('member/money-logs/'+ options.id,{}).then(res=>{
            if(res.statusCode == 200){
               self.setData({
                   balanceDetails:res.data.data,
                   money: (res.data.data.money).toFixed(2),
                   dist_money: (res.data.data.dist_money).toFixed(2)
               })
            }
         })
     }
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
  
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
  
  },


})