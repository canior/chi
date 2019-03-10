// pages/user/sales/detail.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    options: null,
    groupUserOrder: null,
    imgUrlPrefix: app.globalData.imgUrlPrefix,
    carrierName: '',
    trackingNo: '',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu()
    app.buriedPoint(options)
    this.setData({
      options: options
    })
  },

  getGroupUserOrder: function (id) {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/groupUserOrder/view',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
        groupUserOrderId: id
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          that.setData({
            groupUserOrder: res.data.data.groupUserOrder,
            carrierName: res.data.data.groupUserOrder.carrierName,
            trackingNo: res.data.data.groupUserOrder.trackingNo
          })
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) {
      },
      complete(e) { }
    })
  },

  inputCarrierName: function (e) {
    this.setData({
      carrierName: e.detail.value
    })
  },

  inputTrackingNo: function (e) {
    this.setData({
      trackingNo: e.detail.value
    })
  },

  // 确认收货
  deliver: function (e) {
    const that = this;
    if (!this.validation()) return;
    wx.showModal({
      title: '提示',
      content: '您是否确认发货',
      confirmText: '是',
      cancelText: '否',
      success: function (res) {
        if (res.confirm) {
          wx.request({
            url: app.globalData.baseUrl + '/user/groupUserOrder/ship',
            data: {
              thirdSession: wx.getStorageSync('thirdSession'),
              groupUserOrderId: that.data.groupUserOrder.id,
              carrierName: that.data.carrierName,
              trackingNo: that.data.trackingNo
            },
            method: 'POST',
            success: (res) => {
              if (res.statusCode == 200 && res.data.code == 200) {
                console.log(res.data.data)
                that.setData({
                  groupUserOrder: res.data.data.groupUserOrder,
                })
              } else {
                console.log('wx.request return error', res.statusCode);
              }
            },
            fail(e) {
            },
            complete(e) { }
          })
        }
      }
    })
  },

  // 检查输入是否完整
  validation: function () {
    if (!this.data.carrierName) {
      wx.showModal({
        content: '请输入物流商名称',
        showCancel: false,
      });
      return false;
    }
    if (!this.data.trackingNo) {
      wx.showModal({
        content: '请输入物流单号',
        showCancel: false,
      });
      return false;
    }
    return true;
  },  

  // 商品评价
  toUserComment: function (e) {
    wx.navigateTo({
      url: '/pages/user/order/review?id=' + this.data.groupUserOrder.id,
    })
  },

  // 发现更多课程
  toCourse: function (e) {
    wx.reLaunch({
      url: '/pages/course/index'
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
    this.getGroupUserOrder(this.data.options.id)
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