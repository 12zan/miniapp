// pages/appointment/appointment.js
const app = getApp();
const weet = require('../../weet2/weet.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        isTel: 1, //1获取到手机号 2没有获取到手机号
        tel: '',
        isOk: false,
        isSure: false,
        isTime: 0,
        isWho: 0,
        chooseDate: '',
        value: [0, 0],
        multiArray: [
            [],
            []
        ],
        multiIndex: [0, 0],
        multiarray: [
            [],
            []
        ],
        multindex: [0, 0],
        timeAM: '',
        timePM: '',
        isShow: 0,
        isChoose: 0,
        index: 0,
        list: [],
        isShare: false,
        time:[]
    },
    // 处理未来七天的函数
    dealTime: function(num) { // num：未来天数
        var time = new Date() // 获取当前时间日期
        var date = new Date(time.setDate(time.getDate() + num)).getDate() //这里先获取日期，在按需求设置日期，最后获取需要的
        var month = time.getMonth() + 1 // 获取月份
        let day = month + '月' + date + '日'
        return day // 返回对象
    },


    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        if (JSON.stringify(options) != '{}') {
            this.setData({
                options: options
            })
        }
        weet.login(() => {
            weet.event.onLoad.call(app, this,options);
        })
        let self = this;
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
                if (res.data.data.shopInfo.isOpenStaffOrder == true) {
                    if (JSON.stringify(options) != '{}') {
                        self.setData({
                            isShare: true
                        })
                    }
                    if (!res.data.data.member.phone) {
                        self.setData({
                            isTel: 2
                        })
                    } else {
                        self.setData({
                            isTel: 1,
                            telphone: res.data.data.member.phone

                        })
                    }
                    self.setData({
                        timeAM: parseInt(res.data.data.shopInfo.orderStaffTime.start.split(':')[0]),
                        timePM: parseInt(res.data.data.shopInfo.orderStaffTime.end.split(':')[0]),
                        vip: res.data.data.member
                    })
                    let time = []
                    if (self.data.timeAM < self.data.timePM){
                        for (let i = self.data.timeAM; i < self.data.timePM; i++) {
                            let timer = i + ':00~' + (i + 1) + ':00'
                            time.push(timer)

                        }
                        self.setData({
                            time: time,
                        })
                    }
                    if (self.data.timeAM > self.data.timePM){
                        for (let i = self.data.timeAM; i < 24; i++) {
                            let timer = i + ':00~' + (i + 1) + ':00'
                            time.push(timer)
                        }
                        self.setData({
                            time: time,
                        })
                        for (let j = 0; j < self.data.timePM; j++){
                            let timer = j + ':00~' + (j + 1) + ':00'
                           self.data.time.push(timer)
                        }
                        self.setData({
                            time: self.data.time,
                        })
                    }
                  
                } else {
                    wx.showModal({
                        title: '提示',
                        content: '预约功能已关闭！',
                        showCancel: false,
                        confirmText: '确定',
                        success: function(res) {
                            if (res.confirm) {
                                wx.reLaunch({
                                    url: '../index/index',
                                })
                            }
                        },

                    })
                }
            
        })
        let arr = []
        for (let i = 0; i < 8; i++) {
            arr.push(self.dealTime(i))
        }
        arr[0] = '今天'
        arr[1] = arr[1] + ' 明天'
        arr[2] = arr[2] + ' 后天'

        self.setData({
            arr: arr,

        })
    },
    formId: function(e) {
        let formId = e.detail.formId;
        weet.zan.post('save-formid', {
            formId: formId
        })
    },
    bindinput: function(e) {
        this.setData({
            telphone: e.detail.value,
        })
    },
    getPhoneNumber: function(e) {
        let self = this
        // weet.login(() => {
            if (e.detail.errMsg == 'getPhoneNumber:ok') {
                wx.checkSession({
                    success: function() {
                        weet.zan.post('fix-phone', {
                            phone: e.detail.encryptedData,
                            iv: e.detail.iv
                        }).then(res => {
                            if (res.statusCode == 200) {
                                self.setData({
                                    isTel: 1,
                                    telphone: res.data.data.phone
                                })
                            } else {
                                wx.showModal({
                                    title: '提示',
                                    content: res.data.msg,
                                    showCancel: false
                                })
                            }

                        })
                        //session_key 未过期，并且在本生命周期一直有效
                    },
                    fail: function() {
                        weet.login(() => {}) //重新登录
                    }
                })


            }
        // })
    },
    changeNum: function(e) {
        this.setData({
            tel: e.detail.value
        })
    },
    toCheckDate: function() {
        this.setData({
            isShow: 1,
            isTime: 1
        })
    },
    cancel: function() {
        this.setData({
            isShow: 0,
            isTime: 0,
            isChoose: 0,
            isWho: 0,
            chooseData:''
        })
    },
    toCheckOne: function() {
        this.setData({
            isChoose: 1,
            isWho: 1,
        })
    },
    formId: function(e) {
        let formId = e.detail.formId;
        weet.zan.post('save-formid', {
            formId: formId
        })
    },
    bindChange: function(e) {
        const val = e.detail.value
        let one
        for (let i in this.data.arr) {
            if (this.data.arr[val[0]] == this.data.arr[i]) {
                if (i < 3) {
                    if (i == 0) {
                        let date = new Date()
                        let data = date.getDate()
                        let month = date.getMonth() + 1
                        one = month + '月' + data + '日'
                    } else {
                        one = this.data.arr[i].split(' ')[0]
                    }
                } else {
                    one = this.data.arr[i]
                }
            }
        }
        this.setData({
            one: one,
            two: this.data.time[val[1]],
            isOk: true,
            isSure: false,
            chooseDate: this.data.one + ' ' + this.data.two,
        })

    },
    bindChangeOne: function(e) {
        const val = e.detail.value
        for (let i in this.data.array) {
            if (this.data.array[val[0]].split(' ')[0] == this.data.list[i].name) {
                this.setData({
                    key: this.data.list[i].id
                })
            }
        }
        this.setData({
            isOk: false,
            isSure: true,
            three: this.data.array[val[0]].split(' ')[0],
            name: this.data.three
        })
    },
    sure: function() {
        if (this.data.isOk == true) {
            this.setData({
                isTime: 0,
                isShow: 1,
                isWho: 0,
                chooseDate: this.data.one + ' ' + this.data.two,
                isOk: !this.data.isOk
            })
        } else {
            let date = new Date()
            let data = date.getDate()
            let month = date.getMonth() + 1
            let one = month + '月' + data + '日'
            this.setData({
                isTime: 0,
                isShow: 1,
                one:one,
                two: this.data.time[0],
                chooseDate: one + ' ' + this.data.time[0],
                isOk: !this.data.isOk
            })
       
        }
    },
    sure1: function() {
        if (this.data.isSure == true) {
            this.setData({
                isChoose: 1,
                isWho: 0,
                name: this.data.three,
            })
        }
        if (this.data.isSure == false) {
            this.setData({
                isChoose: 1,
                isWho: 0,
                key: this.data.list[0].id,
                name: this.data.array[0].split(' ')[0],
            })
        }

    },
    // 提交预约
    toSubmit: function() {
        let self = this
        if (!self.data.telphone) {
            return wx.showModal({
                title: '提示',
                content: '请先获取手机号',
                showCancel: false
            })
        }
        if (self.data.telphone.length < 11) {
            return wx.showModal({
                title: '提示',
                content: '请输入正确的手机号',
                showCancel: false
            })
        }
        if (!self.data.chooseDate) {
            return wx.showModal({
                title: '提示',
                content: '请选择预约时间',
                showCancel: false
            })
        }
        if (!self.data.name) {
            return wx.showModal({
                title: '提示',
                content: '请选择发型师',
                showCancel: false
            })
        }
        let date = self.data.chooseDate.split(' ')[0]
        let y = new Date().getFullYear()
        let time = self.data.chooseDate.split(' ')[1]
        let s_t = time.split('~')[0].split(':')[0]
        let e_t = time.split('~')[1].split(':')[0]
        if (s_t < 10) {
            s_t = '0' + s_t
        }
        if (e_t < 10) {
            e_t = '0' + e_t
        }
        let m = date.split('月')[0];
        let d = date.match(/月(\S*)日/)[1];
        let start_at = `${y}-${m}-${d} ${s_t+':00'+':00'}`
        let end_at = `${y}-${m}-${d} ${e_t + ':00' + ':00'}`
        weet.zan.post('staff-order', {
            phone: self.data.telphone,
            start_at: start_at,
            end_at: end_at,
            key: self.data.key
        }).then(res => {
            if (res.statusCode == 200) {
                if (res.data.status == 'success') {
                    wx.redirectTo({
                        url: '../reservationRes/reservationRes?result=1',
                    })
                } else {
                    wx.redirectTo({
                        url: '../reservationRes/reservationRes?result=2',
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

    },
    toIndex: function() {
        wx.reLaunch({
            url: '../index/index',
        })
    },
    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function() {
        weet.login(() => {
            weet.event.onLoad.call(app, this, this.data.options);
        })
        let self = this
        let userInfo = wx.getStorageSync('userInfo')
        let shopInfo = wx.getStorageSync('shopInfo')
        self.setData({
            userInfo: userInfo,
            shopInfo: shopInfo
        })
        weet.zan.get('staffs-mini', {}).then(res => {
            let array = []
            let list
            for (let i in res.data.data) {
                list = res.data.data[i].name + ' ' + res.data.data[i].role
                array.push(list)
            }
            self.setData({
                array: array,
                list: res.data.data
            })
        })

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

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function() {
        let self = this
        return {
            title: self.data.shopInfo.name,
            path: '/pages/authorization/authorization?isShare=3&scene=' + app.globalData.encOpenId
        }
    }
})