// pages/businessBuckle/businessBuckle.js
const app = getApp();
const weet = require('../../weet2/weet.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    money:'',
    isStatus:0,//0表示未输入金额 1表示余额不足，不能支付 2表示正常情况
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
      let buckleData = wx.getStorageSync('buckleData')
      let payCode = wx.getStorageSync('payCode')
      wx.getSetting({
          success: function (res) {
              if (!res.authSetting['scope.userInfo']) {
                  wx.redirectTo({
                      url: '../authorization/authorization'
                  })
              }
          }
      })
      this.setData({
          buckleData: buckleData,
          payCode: payCode
      })
  },
    bindblur:function (e){
                this.setData({
                    money: e.detail.value
     })
     
    },
 
    bindReplaceInput: function (e){
        if(e.detail.value.length == 0){
           this.setData({
               isStatus:0,
           })
        }
        else if (e.detail.value > this.data.buckleData.userInfo.money){
            this.setData({
                isStatus: 1
            })
        }
        else{
                this.setData({
                    money: e.detail.value,
                    isStatus: 2
                })
           
        }
                
        
           
    },
        toPay:function(){
            let self = this
                if (self.data.money > self.data.buckleData.userInfo.money && self.data.money< 0) {
                    self.setData({
                        isStatus: 1
                    })
                } else {
                    weet.zan.post('staff/receipt', {
                        code: self.data.payCode,
                        uOpenid: self.data.buckleData.userInfo.wx_user.open_id,
                        key: self.data.buckleData.key,
                        money: self.data.money
                    }).then(res => {
                        if (res.statusCode == 200) {
                            if (res.data.status == 'failed') {
                                wx.reLaunch({
                                    url: '../buckle/buckle?isOk=2&tips=' + res.data.msg,
                                })
                            } if (res.data.status == 'success') {
                                wx.reLaunch({
                                    url: '../buckle/buckle?isOk=1&totalPrice='+ self.data.money,
                                })
                            }
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