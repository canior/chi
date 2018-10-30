// pages/product/detail.js
const app = getApp()
const productReview = require('../tmpl/productReview.js');
Page({
  /**
   * 页面的初始数据
   */
  data: {
    isLogin: false,
    imgUrlPrefix: app.globalData.imgUrlPrefix,    
    product: [],
    productReviewData: {},
    showModal: false,
    btnDisabled: false //防止连击button
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    const productId = options.id ? options.id : 2;
    this.getProduct(productId);
    const url = app.globalData.baseUrl + '/products/' + productId + '/reviews'
    productReview.init(this, url);
    this.setData({
      isLogin: app.globalData.isLogin
    })
  },

  getProduct: function (id) {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/products/' + id,
      data: {
      },
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          that.setData({
            product: res.data.data
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

  // 产品评价图片预览
  wxPreviewImage (e) {
    productReview.previewImage(e, this)
  },

  // 转首页
  toHome: function(e) {
    wx.switchTab({
      url: '/pages/product/index',
    })
  },

  // 单独购买提醒弹窗
  showModal: function (e) {
    this.setData({
      showModal: true
    })
  },
  hideModal: function (e) {
    this.setData({
      showModal: false
    })
  },

  // 单独购买
  tapCreateOrder: function (e) {
    if (this.data.isLogin) {
      this.createOrder();
    } else {
      wx.navigateTo({
        url: '/pages/user/login',
      })
    }
  },
  createOrder: function () {
    const that = this;
    wx.showLoading({
      title: '载入中',
      mask: true,
    });
    that.setData({ btnDisabled: true });
    wx.request({
      url: app.globalData.baseUrl + '/groupUserOrder/create',
      data: {
        productId: this.data.product.id,
        thirdSession: wx.getStorageSync('thirdSession'),
      },
      method: 'POST',
      success: (res) => {
        wx.hideLoading();
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          wx.redirectTo({
            url: '/pages/group/pay?orderId=' + res.data.data.groupUserOrder.id,
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

  // 发起拼团
  tpaCreateGroup: function(e) {
    if (this.data.isLogin) {
      this.createGroup();
    } else {
      wx.navigateTo({
        url: '/pages/user/login',
      })
    }
  },
  createGroup: function() {
    const that = this;
    wx.showLoading({
      title: '载入中',
      mask: true,
    });
    that.setData({ btnDisabled: true });
    wx.request({
      url: app.globalData.baseUrl + '/groupOrder/create',
      data: {
        productId: this.data.product.id,
        thirdSession: wx.getStorageSync('thirdSession'),
      },
      method: 'POST',
      success: (res) => {
        wx.hideLoading();
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          wx.redirectTo({
            url: '/pages/group/pay?orderId=' + res.data.data.groupUserOrder.id,
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
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.setData({
      btnDisabled: false,
      isLogin: app.globalData.isLogin
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
    productReview.getNextPage(this)
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})