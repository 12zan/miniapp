// pages/businessPay/businessPay.js
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
      let self = this
      let exchangeData = wx.getStorageSync('exchangeData')
      let exchangeCode = wx.getStorageSync('exchangeCode')
      wx.getSetting({
          success: function (res) {
              if (!res.authSetting['scope.userInfo']) {
                  wx.redirectTo({
                      url: '../authorization/authorization'
                  })
              }
          }
      })
      self.setData({
          exchangeData: exchangeData,
          exchangeCode: exchangeCode
      })
     
  },
    toPay:function (){
        weet.zan.post('staff/sure-exchange-code' ,{
            code: this.data.exchangeCode,
            key: this.data.exchangeData.key
        }).then(res=>{
            if(res.statusCode == 200){
                if (res.data.status == 'success'){
                    wx.reLaunch({
                        url: '../verificationRes/verificationRes?isOk=1',
                    })
                }
                if (res.data.status == 'failed'){
                    wx.reLaunch({
                        url: '../verificationRes/verificationRes?isOk=2&tips=' + res.data.msg,
                    })
                }
               
            }else{
                wx.showModal({
                    title: '提示',
                    content: res.data.msg,
                    showCancel: false
                })
            }
          
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