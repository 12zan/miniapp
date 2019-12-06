// pages/scavenging/scavenging.js
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
  onLoad: function (options) {
  
  },
    toIndex:function(){
       wx.reLaunch({
           url: '../index/index',
       })
    },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
      let self = this
    weet.login(() => {
      weet.zan.post('staff/by-scan', {}).then(res => {
          if (res.statusCode == 200) {
              self.setData({
                  payRes: 1
              })
          } else {
              wx.showModal({
                  title: '提示',
                  content: res.data.msg,
                  showCancel: false
              })
          }
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
  
  },


})