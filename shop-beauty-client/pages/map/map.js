// pages/map/map.js
Page({
    data: {
        latitude: '',
        longitude: ''
    },
    regionchange(e) {},
    markertap(e) {},
    controltap(e) {},
    onLoad: function() {
        let self = this
        wx.getSystemInfo({
            success: function(res) {
                //设置map高度，根据当前设备宽高满屏显示
                self.setData({
                    view: {
                        Height: res.windowHeight
                    }
                })
            }
        })
    },
    onReady: function() {
        let location = wx.getStorageSync('shopInfo')
        this.setData({
            location: location,
            latitude: location.lat,
            longitude: location.lng,
            markers: [{
                iconPath: "../../images/address.png",
                id: 0,
                latitude: location.lat,
                longitude: location.lng,
                width: 30,
                height: 30,
            }],
            polyline: [{
                points: [{
                        longitude: location.lng,
                        latitude: location.lat
                    },
                    {
                        longitude: location.lng,
                        latitude: location.lat
                    }
                ],
                width: 2,
                dottedLine: true
            }],
        })

    }

})