// pages/user/order/detail.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    groupUserOrder: null,
    imgUrlPrefix: app.globalData.imgUrlPrefix,    
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },

  onLoad: function (options) {
    this.getGroupUserOrder(options.id)
  },

  getGroupUserOrder: function (id) {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/user/groupUserOrder',
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

  // 确认收货
  deliver: function (e) {
    const that = this;
    wx.showModal({
      title: '提示',
      content: '您是否确认已收货',
      confirmText: '是',
      cancelText: '否',
      success: function (res) {
        if (res.confirm) {
          wx.request({
            url: app.globalData.baseUrl + '/user/groupUserOrder/post',
            data: {
              thirdSession: wx.getStorageSync('thirdSession'),
              groupUserOrderId: that.data.groupUserOrder.id
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

  // 商品评价
  toUserComment: function (e) {
    wx.navigateTo({
      url: '/pages/user/order/review?id=' + this.data.groupUserOrder.id,
    })
  },

  // 继续拼团
  toProductDetail: function (e) {
    wx.reLaunch({
      url: '/pages/product/detail?id=' + this.data.groupUserOrder.product.id,
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

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})