// pages/rechargeRes/rechargeRes.js
const app = getApp();
const weet = require('../../weet2/weet.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
      list:[],
      isShow:false
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
    if(JSON.stringify(options) != '{}'){
        wx.showLoading({
            title: '充值中...',
        })
        if (options.payRes == 1){
            weet.zan.get('orders/' + options.orderId + '/find', {}).then(res => {
                if (res.statusCode == 200) {
                    self.setData({
                        payRes: 1,
                        totalPrice: options.totalPrice,
                        orderId: options.orderId
                    })
                    wx.hideLoading()
                    weet.zan.get('activity/store-value?condition=' + options.totalPrice, {}).then(res => {
                        if (res.statusCode == 200) {
                            self.setData({
                                list: res.data.data,
                                isShow:true
                            })
                        }
                    })
                } else {
                    self.setData({
                        payRes: 2,
                        tips: res.data.msg
                    })
                }
            })
        }
        if (options.payRes == 2){
            wx.hideLoading()
            self.setData({
                payRes: 2,
                tips: '充值失败，请重新充值！'
            })
        }
       
    }
  },
    toBack:function (){
     wx.navigateBack({
         url: '../recharge/recharge',
     })
    },
    toMy:function(){
        wx.reLaunch({
            url: '../my/my',
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