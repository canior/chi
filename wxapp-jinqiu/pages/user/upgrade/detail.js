// pages/user/upgrade/detail.js
const app = getApp()
const productReview = require('../../tmpl/productReview.js');
const share = require('../../tmpl/share.js');
const bottom = require('../../tmpl/bottom.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    isLogin: false,
    imgUrlPrefix: app.globalData.imgUrlPrefix,
    product: null,
    productReviewData: {},
    bottomData: {},
    shareData: {},
    loading: true
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu()
    const productId = options.id ? options.id : 1;
    this.getProduct(productId);
    app.buriedPoint(options)
  },

  getProduct: function (productId) {
    const that = this;
    const pages = getCurrentPages();
    const currentPageUrl = '/' + pages[pages.length - 1].route;
    wx.showLoading({
      title: '载入中',
    })
    wx.request({
      url: app.globalData.baseUrl + '/user/upgradeUserOrder/' + productId + '/view',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
        url: currentPageUrl
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          var product = res.data.data.product;
          
          product.productSpecImages.forEach((item) => {
            item.loading = true
          })
          
          that.setData({
            product: product
          })
          const url = app.globalData.baseUrl + '/products/' + product.id + '/reviews'
          productReview.init(that, url);          
          share.setShareSources(that, res.data.data.shareSources)
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) {
      },
      complete(e) { wx.hideLoading() }
    })
  },

  imgLoadDone: function (e) {
    //console.log('bindload:imgLoadDone', e)
    const index = e.currentTarget.dataset.index
    this.setData({
      ['product.productSpecImages[' + index + '].loading']: false
    })
  },

  // 购买
  wxCreateOrder: function (e) {
    bottom.createOrder(this, app.globalData.baseUrl + '/groupUserOrder/create', this.data.product.id)
  },

  // 转首页
  wxHome: function (e) {
    wx.switchTab({
      url: '/pages/course/index',
    })
  },
  
  // 分享:邀请好友
  wxShowShareModal: function (e) {
    share.showModal(this)
  },
  wxHideShareModal: function (e) {
    share.hideModal(this)
  },
  wxSaveShareSource: function (e) {
    share.saveShareSource(this, e, app.globalData.baseUrl + '/user/shareSource/create')
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
      isLogin: app.globalData.isLogin
    })
    if (this.data.isLogin) {
      bottom.init(this)
      share.init(this)
      this.setData({ ['product.productSpecImages']: [] })
      this.getProduct()
    } else {
      wx.navigateTo({
        url: '/pages/user/login',
      })
    }
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
  onShareAppMessage: function (res) {
    return share.shareObject(this, res)
  }
})