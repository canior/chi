// pages/product/detail.js
const app = getApp()
const productReview = require('../tmpl/productReview.js');
const share = require('../tmpl/share.js');
const bottom = require('../tmpl/bottom.js');
Page({
  /**
   * 页面的初始数据
   */
  data: {
    isLogin: false,
    imgUrlPrefix: app.globalData.imgUrlPrefix,
    product: {},
    productReviewData: {},
    bottomData: {},
    shareData: {},
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    //app.buriedPoint(options);
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
  wxHome: function(e) {
    wx.switchTab({
      url: '/pages/product/index',
    })
  },

  // 单独购买提醒弹窗
  wxShowModal: function (e) {
    bottom.showModal(this)
  },
  wxHideModal: function (e) {
    bottom.hideModal(this)
  },
  // 单独购买
  wxCreateOrder: function (e) {
    bottom.createOrder(this, app.globalData.baseUrl + '/groupUserOrder/create', this.data.product.id)
  },
  // 发起拼团
  wxCreateGroup: function(e) {
    bottom.createGroup(this, app.globalData.baseUrl + '/groupOrder/create', this.data.product.id)
  },

  // 分享:邀请好友
  wxShowShareModal: function (e) {
    share.showModal(this)
  },
  wxHideShareModal: function (e) {
    share.hideModal(this)
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
    bottom.init(this)
    share.init(this)
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
  onShareAppMessage: function (options) {
    const that = this;
    var pages = getCurrentPages();
    var currentPageUrl = pages[pages.length - 1].route;
    wx.request({
      url: app.globalData.baseUrl + '/user/shareSource/create',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
        page: currentPageUrl,
        shareSourceType: 'refer',
        productId: that.data.product.id,
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          //console.log(res.data.data);
          const shareSource = res.data.data.shareSource;
          console.log(res.data.data.shareSource)
          return {
            title: shareSource.title,
            imageUrl: that.data.imgUrlPrefix + '/' + shareSource.bannerFileId,
            path: shareSource.page
          }
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) {},
      complete(e) {}
    })
    /*console.log('-----------------');
    return {
      title: "分享标题",
      imageUrl: '',
      path: '/pages/group/index'
    }*/
  }
})