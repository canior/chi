// pages/group/pay.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    imgUrlPrefix: app.globalData.imgUrlPrefix,
    groupUserOrder: null,
    btnDisabled: false //防止连击button
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    const orderId = options.orderId ? options.orderId : 100144
    this.getGroupUserOrder(orderId)
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  getGroupUserOrder: function (orderId) {
    const that = this;
    wx.showLoading({
      title: '载入中',
      mask: true,
    });
    wx.request({
      url: app.globalData.baseUrl + '/groupUserOrder/view',
      data: {
        groupUserOrderId: orderId,
      },
      method: 'POST',
      success: (res) => {
        wx.hideLoading();
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          that.setData({
            groupUserOrder: res.data.data.groupUserOrder
          })
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) {
        wx.hideLoading();
      },
      complete(e) { }
    })
  },

  // 转地址管理或新建地址
  toUserAddress: function(e) {
    if (this.data.groupUserOrder.user.defaultAddress) {//地址管理
      wx.redirectTo({
        url: '/pages/user/address/index?orderId=' + this.data.groupUserOrder.id,
      })
    } else {//新建地址
      wx.redirectTo({
        url: '/pages/user/address/edit?orderId=' + this.data.groupUserOrder.id,
      })
    }
  },

  // 付款
  pay: function(e) {
    const that = this;
    wx.showLoading({
      title: '跳转支付',
      mask: true,
    });
    that.setData({ btnDisabled: true });
    wx.request({
      url: app.globalData.baseUrl + '/groupUserOrder/pay',
      data: {
        groupUserOrderId: that.data.groupUserOrder.id,
        thirdSession: wx.getStorageSync('thirdSession'),
      },
      method: 'POST',
      success: (res) => {
        wx.hideLoading();
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          const payment = res.data.data.payment;
          const groupUserOrderId = res.data.data.groupUserOrder.id;
          wx.requestPayment({
            timeStamp: payment.timeStamp.toString(),
            nonceStr: payment.nonceStr,
            package: payment.package,
            signType: payment.signType,
            paySign: payment.paySign,
            success: function (res) {
              wx.request({
                url: app.globalData.baseUrl + '/groupUserOrder/notifyPayment',
                data: {
                  isPaid: true,
                  thirdSession: wx.getStorageSync('thirdSession'),
                  groupUserOrderId: groupUserOrderId,
                },
                method: 'POST',
                success: (res) => {
                  if (res.statusCode == 200 && res.data.code == 200) {
                    console.log(res.data.data)
                    const groupOrderId = res.data.data.groupUserOrder.groupOrderId
                    if (groupOrderId) {//转开团成功页
                      wx.redirectTo({
                        url: '/pages/group/index?id=' + groupOrderId,
                      })
                    } else {//转订单详情页

                    }
                  } else {
                    console.log('wx.request return error', res.statusCode);
                  }
                },
                fail(e) {
                  console.log('wx.request /groupUserOrder/notifyPayment: fail', e)
                },
                complete(e) { }
              })
            },
            fail: function (res) {
              console.log('wx.requestpayment: fail', res)
              wx.showToast({
                title: '支付失败',
              });
              that.setData({ btnDisabled: false });
            },
            complete: function (res) { }
          })
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) {
        wx.hideLoading();
        that.setData({ btnDisabled: false });
      },
      complete(e) { }
    })
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.setData({
      btnDisabled: false
    })
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

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})