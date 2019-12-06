// pages/settlement/settlement.js
const app = getApp();
const weet = require('../../weet2/weet.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        isShow: 1, //1表示选中 2表示未选中
        isCheck: 2, //1表示选中 2表示未选中
        count: 1,
        totalPrice: '',
        price: '',
        isVip: '', //是否为会员,
        isStaffWxPay: '', //会员价是否能使用微信支付
        detailsData: [],
        payMethod: 2,//选择哪种支付方式 2表示余额支付 1表示微信支付
        isPay:'',
        priceType:1,
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
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
        weet.zan.get('preload', {}).then(res => {
            self.setData({
                vip: res.data.data.member,
                shopInfo: res.data.data.shopInfo
            })
            if (res.data.data.member.level != 0) {
                self.setData({
                    isVip: true,
                })
                if (res.data.data.shopInfo.memberMustUseWxPay == true) {
                    self.setData({
                        isStaffWxPay: true
                    })
                }
                else{
                    self.setData({
                        isStaffWxPay: false
                    })
                }
            }
            if (res.data.data.member.level == 0) {
                self.setData({
                    isVip: false,
                })
            }
            if (JSON.stringify(options) !== '{}') {
                self.setData({
                    id: options.id
                })
                weet.zan.get('items/' + options.id, {}).then(res => {
                    if (res.statusCode == 200) {
                        self.setData({
                            detailsData: res.data.data
                        })
                        self.checkOut()
                    } else {
                        wx.showModal({
                            title: '提示',
                            content: res.data.msg,
                            showCancel: false
                        })
                    }
                })
            }
        })


    },
    getPhoneNumber: function (e) {
        let self = this
        // weet.login(() => {
            if (e.detail.errMsg == 'getPhoneNumber:ok') {
                wx.checkSession({
                    success: function () {
                        weet.zan.post('fix-phone', {
                            phone: e.detail.encryptedData,
                            iv: e.detail.iv
                        }).then(res => {
                            if(res.statusCode == 200){
                                self.setData({
                                    ['vip.phone']: res.data.data.phone
                                })
                            }else{
                                wx.showModal({
                                    title: '提示',
                                    content: '请重试',
                                    showCancel: false,
                                })
                            }
                           
                        })
                        //session_key 未过期，并且在本生命周期一直有效
                    },
                    fail: function () {
                        weet.login(() => { })
                    }
                })
            }
            // })
        },
    checkOut: function() {
        let self = this
        // //选择哪种支付方式 2表示余额支付 1表示微信支付
        if (self.data.payMethod == 2 ) {
            // if (self.data)
            if (self.data.isStaffWxPay == true && self.data.isVip == true){
                self.setData({
                    price: self.data.detailsData.member_price,
                    totalPrice: (self.data.detailsData.member_price * self.data.count).toFixed(2) ,
                    // priceType: 2,//会员价
                    payMethod: 2,//余额支付
                })
            } else if (self.data.isStaffWxPay == false && self.data.isVip == true){
                self.setData({
                    price: self.data.detailsData.member_price,
                    totalPrice: (self.data.detailsData.member_price * self.data.count).toFixed(2),
                    // priceType: 2,//会员价
                    payMethod: 2,//余额支付
                })
            }
            else{
                self.setData({
                    price: self.data.detailsData.price,
                    totalPrice: (self.data.detailsData.price * self.data.count).toFixed(2),
                    // priceType: 2,//会员价
                    payMethod: 2,//余额支付
                })
            }
        

        }
        if (self.data.payMethod == 1) {
            if (self.data.isStaffWxPay == true && self.data.isVip == true) {
                self.setData({
                    price: self.data.detailsData.price,
                    totalPrice: (self.data.detailsData.price * self.data.count).toFixed(2) ,
                    // priceType: 1,//原价
                    payMethod: 1,//微信支付
                })
            } 
            else if (self.data.isStaffWxPay == false && self.data.isVip == true) {
                self.setData({
                    price: self.data.detailsData.member_price,
                    totalPrice: (self.data.detailsData.member_price * self.data.count).toFixed(2) ,
                    // priceType: 2,//会员价
                    payMethod: 2,//余额支付
                })
            }
            else {
                self.setData({
                    price: self.data.detailsData.price,
                    totalPrice: (self.data.detailsData.price * self.data.count).toFixed(2),
                    // priceType: 1,//原价
                    payMethod: 1,//微信支付
                })
            }
        }
    },
    formId: function(e) {
        let formId = e.detail.formId;
        weet.zan.post('save-formid', {
            formId: formId
        })
    },
    toPay: function() {
        let self = this
        if (!self.data.vip.phone){
            wx.showModal({
                content: '请先授权获取手机号，以便给您提供更完整的服务！',
                showCancel:false
            })
            return
        }
        weet.zan.post('orders', {
            serverId: self.data.id,
            count: self.data.count,
            phone: self.data.vip.phone,
            payMethod: self.data.payMethod,
            priceType: self.data.priceType
        }).then(res => {
            if (res.statusCode == 200) {
                self.setData({
                    orderId: res.data.data.orderId
                })
                if (self.data.payMethod == 1) {
                    weet.zan.post('pay/wx', {
                        orderId: res.data.data.orderId
                    }).then(res => {
                        if (res.statusCode == 200) {
                            wx.requestPayment({
                                'timeStamp': res.data.timestamp,
                                'nonceStr': res.data.nonce_str,
                                'package': 'prepay_id=' + res.data.prepay_id,
                                'signType': 'MD5',
                                'paySign': res.data.sign,
                                'success': function (res) {
                                    wx.redirectTo({
                                        url: '../pay/pay?payRes=1&orderId=' + self.data.orderId + '&totalPrice=' + self.data.totalPrice +'&type=wx',
                                    })
                                },
                                'fail': function (res) {
                                    wx.navigateTo({
                                        url: '../pay/pay?payRes=2&isNoMoney=0&type=wx',
                                    })
                                }
                            })
                        }

                    })
                }
                if (self.data.payMethod == 2) {
                    if(self.data.totalPrice < self.data.vip.money){
                        wx.showModal({
                            content: '账户余额: ¥' + self.data.vip.money + '\r\n' + '待支付: ¥' + self.data.totalPrice + '\r\n' +'\r\n' + '是否确认支付?',
                            showCancel:true,
                            confirmText: '确认支付',
                            cancelText: '取消',
                            success: function (res) {
                                if (res.confirm) {
                                    weet.zan.post('pay/store', {
                                        orderId: self.data.orderId
                                    }).then(res => {
                                        if (res.statusCode == 200) {
                                            wx.redirectTo({
                                                url: '../pay/pay?payRes=1&orderId=' + self.data.orderId + '&totalPrice=' + self.data.totalPrice + '&type=store',
                                            })
                                        }
                                        if (res.data.code == '405') {
                                            wx.navigateTo({
                                                url: '../pay/pay?payRes=2&isNoMoney=0&type=store',
                                            })
                                        }

                                    })
                                } 
                            }
                        })
                    }else{
                        wx.showModal({
                            content: '您的账户余额不足，请充值或返回选用微信支付！',
                            showCancel: true,
                            confirmText: '去充值',
                            cancelText: '返回',
                            success: function (res) {
                                if (res.confirm) {
                                    wx.navigateTo({
                                        url: '../recharge/recharge',
                                    })
                                } 

                            },
                        })
                    }
                  
                }


            } else {
                wx.showModal({
                    title: '提示',
                    content: res.mag,
                    showCancel: false,
                    complete: function(res) {
                        wx.navigateTo({
                            url: '../pay/pay?payRes=2'
                        })
                    },
                })
            }
        })

    },
    toChoose: function() {
        if (this.data.isStaffWxPay == true && this.data.isVip == true){
            this.setData({
                priceType:1,
                isShow: 1,
                isCheck: 2,
                payMethod: 2,
            })
        }
        else if (this.data.isStaffWxPay == false && this.data.isVip == true){
            this.setData({
                isShow: 1,
                isCheck: 2,
                payMethod: 2,
                priceType:1,
            })
        }
        else{
            this.setData({
                isShow: 1,
                isCheck: 2,
                payMethod: 2,
                priceType: 2,
            })
        }
        this.checkOut()

    },
    toCheck: function() {
        if (this.data.isStaffWxPay == true && this.data.isVip == true) {
            this.setData({
                isShow: 2,
                isCheck: 1,
                payMethod: 1,
                priceType:2
            })
        } else if (this.data.isStaffWxPay == false && this.data.isVip == true){
            this.setData({
                isShow: 2,
                isCheck: 1,
                payMethod: 1,
                priceType: 1
            })
        }else{
            this.setData({
                isShow: 2,
                isCheck: 1,
                payMethod: 1,
                priceType: 2
            })
        }
      
        this.checkOut()
        if (this.data.isStaffWxPay == true) {
            wx.showModal({
                title: '提示',
                content: '使用微信支付将无法享受会员价',
                showCancel: false
            })
        }


    },
    reduce: function() {
        if (this.data.count > 1) {
            let count = this.data.count - 1;
            let totalPrice = this.data.totalPrice - this.data.price
            this.setData({
                count: count,
                totalPrice: totalPrice.toFixed(2)
            })
        }

    },
    add: function() {
        if (this.data.count < 100) {
            let count = this.data.count + 1;
            let totalPrice = count * this.data.price
            this.setData({
                count: count,
                totalPrice: totalPrice.toFixed(2)
            })
        }

    },
    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function() {

    },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function() {

    },

    /**
     * 生命周期函数--监听页面隐藏
     */
    onHide: function() {

    },

    /**
     * 生命周期函数--监听页面卸载
     */
    onUnload: function() {

    },

    /**
     * 页面相关事件处理函数--监听用户下拉动作
     */
    onPullDownRefresh: function() {

    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function() {

    },

  
 
})