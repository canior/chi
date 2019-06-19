// pages/user/order/index.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    menu: [
      { name: '全部', status: null },
      { name: '待发货', status: 'pending' },
      { name: '已发货', status: 'shipping' },
      { name: '已收货', status: 'delivered' }
    ],
    curStatus: null,
    groupUserOrders: [],
    imgUrlPrefix: app.globalData.imgUrlPrefix,    
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu()
    app.buriedPoint(options)
    this.setData({
        curStatus: options.status ? options.status : null
    })
  },

  getGroupUserOrders: function (status) {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/user/groupUserOrders/',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
        groupUserOrderStatus: status,
        productType: 'product'
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          that.setData({
            groupUserOrders: res.data.data.groupUserOrders,
            curStatus: status
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

  tapMenu: function (e) {
    this.getGroupUserOrders(e.currentTarget.dataset.status)
  },

  // 转订单详情
  toUserOrderDetail: function (e) {
    const orderId = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '/pages/user/order/detail?id=' + orderId,
    })
  },

  // 转产品详情
  toProductDetail: function (e) {
    const productId = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '/pages/product/detail?id=' + productId,
    })
  },

  // 确认收货
  deliver: function (e) {
    const orderId = e.currentTarget.dataset.id;
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
              groupUserOrderId: orderId
            },
            method: 'POST',
            success: (res) => {
              if (res.statusCode == 200 && res.data.code == 200) {
                console.log(res.data.data)
                that.getGroupUserOrders(that.data.curStatus)
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
    const orderId = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '/pages/user/order/review?id=' + orderId,
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
    this.getGroupUserOrders(this.data.curStatus)
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