// pages/qrCode/qrCode.js
const app = getApp();
const weet = require('../../weet2/weet.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    code:''
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
      weet.zan.get('member/pay-qrcode', {}).then(res => {
          if (res.statusCode == 200) {
              self.setData({
                  qrCode: res.data.data.image,
                  code: res.data.data.code
              })
              wx.connectSocket({
                  url: 'wss://ws.z.12zan.net/shop-beauty/' + self.data.code
              })
              wx.onSocketOpen(res => {
              })
              wx.onSocketMessage(function (res) {
            
                  let result = (res.data).replace(/\ufeff/g, "")
                  let results = JSON.parse(result)
                  if (results.status == 'success') {
                      wx.closeSocket()
                      wx.reLaunch({
                          url: '../userBuckleRes/userBuckleRes?payRes=1&totalPrice=' + results.money + '&id=' + results.id,
                      })
                  }
                  if (results.status == 'failed') {
                      wx.reLaunch({
                          url: '../userBuckleRes/userBuckleRes?payRes=2',
                      })
                  }
             
              })
              wx.onSocketError(function (res) {
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
    toRefresh: function(){
        let self = this
        weet.zan.get('member/pay-qrcode?code='+ self.data.code, {}).then(res => {
            if (res.statusCode == 200) {
                self.setData({
                    code: res.data.data.code,
                    qrCode:res.data.data.image
                })
                wx.showToast({
                    title: '刷新成功',
                })
                wx.closeSocket()
                wx.connectSocket({
                    url: 'wss://ws.z.12zan.net/shop-beauty/' + self.data.code
                })
                wx.onSocketOpen(res => {
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
    toPre: function (){
        wx.navigateBack({
            delta: 2
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
  
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
  
  },

})