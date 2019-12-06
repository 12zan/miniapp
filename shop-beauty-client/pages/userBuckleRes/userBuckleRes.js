// pages/userBuckleRes/userBuckleRes.js
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
        wx.getSetting({
            success: function (res) {
                if (!res.authSetting['scope.userInfo']) {
                    wx.redirectTo({
                        url: '../authorization/authorization'
                    })
                }
            }
        })
        if (JSON.stringify(options) !== '{}') {
            if (options.payRes == 1) {
                this.setData({
                    payRes: 1,
                    totalPrice: options.totalPrice,
                    id: options.id
                })
            }
            if (options.payRes == 2) {
                this.setData({
                    payRes: 2,
                    tips: '余额不足，请先充值'

                })
            }
        }
    },
    toBalanceDetails: function () {
        let self = this
        wx.navigateTo({
            url: '../balanceDetails/balanceDetails?id=' + self.data.id,
        })
    },
    toRecharge: function (){
        wx.navigateTo({
            url: '../recharge/recharge',
        })
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