// pages/authorization/authorization.js
const app = getApp();
const weet = require('../../weet2/weet.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
      isAuthSetting: true,
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
      let self = this
      self.setData({
          options: options
      })
      wx.showLoading({
          title: '加载中',
          mask:true
      })
      wx.getSetting({
          success: function (res) {
              if (!res.authSetting['scope.userInfo']) {
                  self.setData({
                      isAuthSetting: true
                  })
                  if (options.isShare != undefined){
                      self.setData({
                          share: true
                      })
                      wx.hideLoading()
                  }else{
                      self.setData({
                          share: false
                      })
                      wx.hideLoading()
                  }
              }else{
                  self.setData({
                      isAuthSetting: false
                  })
                  if (options.isShare == 1) {
                      wx.reLaunch({
                          url: '../index/index?isShare=1&scene=' + options.scene,
                      })
                  }
                  else if (options.isShare == 2) {
                      wx.reLaunch({
                          url: '../hairStylist/hairStylist?isShare=2&scene=' + options.scene,
                      })
                  }
                  else if (options.isShare == 3) {
                      wx.reLaunch({
                          url: '../appointment/appointment?isShare=3&scene=' + options.scene,
                      })
                  }
                  else if (options.isShare == 4) {
                      wx.reLaunch({
                          url: '../serviceDetails/serviceDetails?isShare=4&id=' + options.id + '&scene=' + options.scene,
                      })
                  }
                  else if (options.isShare == 5) {
                      wx.reLaunch({
                          url: '../index/index?scene=' + options.scene
                      })
                  }
                  else if (options.isShare == 6) {
                      wx.reLaunch({
                          url: '../recharge/recharge?scene=' + options.scene
                      })
                  } else {
                      wx.reLaunch({
                          url: '../index/index',
                      })
                  }
              }
          },
      })
     
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
                    weet.login(() => {
                    if(self.data.isShare == 1){
                        wx.reLaunch({
                            url: '../index/index?isShare=1&scene=' + self.data.scene,
                        })
                    }
                    else if (self.data.isShare == 2){
                        wx.reLaunch({
                            url: '../hairStylist/hairStylist?isShare=2&scene=' + self.data.scene,
                        })
                    }
                else if (self.data.isShare == 3) {
                        wx.reLaunch({
                            url: '../appointment/appointment?isShare=3&scene=' + self.data.scene,
                        })
                    }
                  else if (self.data.isShare == 4) {
                        wx.reLaunch({
                            url: '../serviceDetails/serviceDetails?isShare=4&id=' + self.data.id + '&scene=' + self.data.scene,
                        })
                    }
                  else if (self.data.isShare == 5) {
                        wx.reLaunch({
                            url: '../my/my?scene=' + self.data.scene
                        })
                    }
                   else if (self.data.isShare == 6) {
                        wx.reLaunch({
                            url: '../recharge/recharge?scene=' + self.data.scene
                        })
                    }else{
                        wx.reLaunch({
                            url: '../index/index',
                        })
                    }
                })
                })
            } 
        }
        openSetting();
    },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
      let self = this
      weet.login(() => {
          weet.zan.get('home/show', {}).then(res => {
              if (res.statusCode == 200) {
                  self.setData({
                      shopInfo: res.data.data.shopInfo

                  })
              }
          })
      })
      if (JSON.stringify(self.data.options) != '{}') {
          self.setData({
              isShare: self.data.options.isShare,
              id: self.data.options.id,
              scene: self.data.options.scene
          })
      }
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